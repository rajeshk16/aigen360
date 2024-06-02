<?php 

namespace Modules\OpenAI\Services\v2;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\OpenAI\Entities\Archive;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Exception;

class ArchiveService
{
    private $archive;

    public function __construct() 
    {
        $this->archive = new Archive();
    }

    /**
     * Get archived items with optional filtering by type.
     *
     * @param string|null $type The type of archived items to filter by (optional).
     * @return \Illuminate\Database\Eloquent\Builder The query builder instance.
     */
    protected function get(?string $type = null): Builder
    {
        return $this->archive->with('metas')->when($type !== null, function($query) use ($type) {
            return $query->where('type', $type);
        });
    }

    /**
     * Display the specified archived item.
     *
     * @param mixed $id The ID of the item to display.
     * @param string|null $type The type of archived items to filter by (optional).
     * @return \Illuminate\Database\Eloquent\Model The archived item.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the item is not found.
     */
    protected function find($id, ?string $type = null): Model
    {
        return $this->archive->with('metas')->when($type !== null, function($query) use ($type) {
            return $query->where('type', $type);
        })->findOrFail($id);
    }

    /**
     * Create a new archived item with associated metadata.
     *
     * @param array $data The data for creating the archived item and its metadata.
     * @return \Illuminate\Database\Eloquent\Model The newly created archived item.
     * @throws \Exception If an error occurs during creation.
     */
    protected function create(array $data): Model
    {
        [$mainData, $metaData] = $this->extractData($data);

        DB::beginTransaction();

        try {
            // Create the main model instance
            $newModel = $this->archive->create($mainData);

            // Attach metadata if provided in the data
            if (isset($metaData)) {
                $this->attachMetadata($newModel, $metaData);
            }

            DB::commit();

            return $newModel;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing archived item and its associated metadata.
     *
     * @param array $data The data for updating the archived item and its metadata.
     * @param mixed $id The ID of the item to update.
     * @return \Illuminate\Database\Eloquent\Model The updated archived item.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the item is not found.
     * @throws \Exception If an error occurs during update.
     */
    protected function update(array $data, $id): Model
    {
        [$mainData, $metaData] = $this->extractData($data);

        DB::beginTransaction();

        try {
            $existingModel = $this->archive->findOrFail($id);
            $existingModel->update($mainData);

            if (isset($metaData)) {
                $this->attachMetadata($existingModel, $metaData);
            }

            DB::commit();

            return $existingModel;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an archived item with optional type filtering.
     *
     * @param mixed $id The ID of the item to delete.
     * @param string|null $type The type of archived items to filter by (optional).
     * @return \Illuminate\Database\Eloquent\Model The deleted archived item.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the item is not found.
     * @throws \Exception If an error occurs during deletion.
     */
    protected function delete($id, ?string $type = null): Model|Exception
    {
        DB::beginTransaction();

        try {
            $archive = $this->archive->where('id', $id)->when($type !== null, function($query) use ($type) {
                return $query->where('type', $type);
            })->first() ?? throw new Exception(__(':x does not exist.', ['x' => __('Image')]), Response::HTTP_NOT_FOUND);

            $archive->unsetMeta(array_keys($archive->getMeta()->toArray()));  
            $archive->save();  

            $archive->delete();

            DB::commit();

            return $archive;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function attachMetadata(Archive $archive, array $metaData)
    {
        foreach ($metaData as $key => $value) {
            $archive->setMeta($key, $value);
            $archive->save();
        }
    }

    private function extractData(array $data)
    {
        $mainData = [];
        $metaData = [];

        // Get the model's table columns
        $tableColumns = $this->archive->getConnection()->getSchemaBuilder()->getColumnListing($this->archive->getTable());

        foreach ($data as $key => $value) {
            if (in_array($key, $tableColumns)) {
                // Key matches a column in the main table
                $mainData[$key] = $value;
            } else {
                // Key does not match a column, consider it as metadata
                $metaData[$key] = $value;
            }
        }

        return [$mainData, $metaData];
    }

    public static function __callStatic($method, $args)
    {
        $calledClass = get_called_class();
        $archiveClass = new $calledClass;
        
        if (method_exists($archiveClass, $method) && is_callable([$archiveClass, $method])) {
            // Call the method dynamically
            return $archiveClass->{$method}(...$args);
        } else {
            throw new \BadMethodCallException("Static method $method not found");
        }
    }
}
