<?php

namespace Modules\OpenAI\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\MediaManager\Http\Models\ObjectFile;
use App\Traits\ModelTraits\hasFiles;
use App\Traits\ModelTraits\Metable;
use App\Traits\ModelTraits\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTrait;

class Archive extends Model
{
    use HasFactory;
    use hasFiles;
    use Metable;
    use ModelTrait;
    use Filterable;

    /**
     * The table associated with the model's meta data.
     *
     * @var string
     */
    protected $metaTable = 'archives_meta';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Relation with User model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * Clears the footprints associated with the given Archive.
     *
     * This method removes any records from the ObjectFile table where the object_type is 'archives'
     * and the object_id matches the ID of the provided Archive object.
     *
     * @param Archive $archive The Archive object for which footprints are to be cleared.
     * @return void
     */
    public static function clearFootprints(Archive $archive): void
    {
        ObjectFile::where(['object_type' => 'archives', 'object_id' => $archive->id])->delete();
    }

    /**
    * Define a relationship between Archive and ChatBot models.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function chatbot()
    {
        return $this->belongsTo(ChatBot::class, 'bot_id');
    }

    /**
     * Retrieve embedded resources associated with the file IDs stored in metadata.
     *
     * This method retrieves embedded resources based on the file IDs stored in the metadata
     * of the current instance. It queries the database to fetch embedded resources matching
     * the provided file IDs and returns them along with associated metadata, users, and child resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\EmbededResource[]
     */
    public function file()
    {
        $ids = $this->metas()->where('key', 'file_id')->first()->value ?? null;
        return EmbededResource::with(['metas', 'user', 'childs'])->whereIn('id', explode(',',  $ids))->get();
    }

    
    /**
     * DocChat list
     *
     * @return Object
     */
    public function history(): Object
    {
        $archive = Archive::with('metas')->whereNull('parent_id')->whereIn('type', ['chat', 'file', 'url']);

        $userRole = auth()->user()->roles()->first();

        if ($userRole->type == 'user') {
            $archive = $archive->where(['user_id' => auth('api')->user()->id]);
        }

        if (request('id')) {
            $archive = $archive->where('id', request('id'));
        }
        return $archive->orderBy('created_at', 'desc');
    }

    /**
     * Get content by ID.
     *
     * @param  mixed  $id The ID of the content.
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function contentById($id): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model()->with(['user', 'childs'])->where(['parent_id' => $id]);
    }

    /**
     * Get model instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function model(): \Illuminate\Database\Eloquent\Builder
    {
        return Archive::with('metas');
    }

    /**
     * Define a relation with child EmbededResource models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany('Modules\OpenAI\Entities\Archive', 'parent_id')->with(['user', 'metas', 'childs', 'file']);
    }
}
