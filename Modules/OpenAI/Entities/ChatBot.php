<?php

namespace Modules\OpenAI\Entities;

use App\Models\Model;
use App\Traits\ModelTrait;
use App\Traits\ModelTraits\hasFiles;
use Modules\MediaManager\Http\Models\ObjectFile;

class ChatBot extends Model
{
    use hasFiles;
    use ModelTrait;

    /**
     * The table associated with the model table.
     *
     * @var string
     */
    protected $table = 'chat_bots';

    /**
     * Eager loading data.
     *
     * @var string
     */
    protected $with = ['chatCategory'];

    /**
     * Fillable fields for mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'chat_category_id',
        'name',
        'code',
        'message',
        'role',
        'promt',
        'status',
        'is_default',
    ];

    /**
     * Store a new chat bot.
     *
     * @param  array  $data
     * @param  bool   $isReturnedId
     * @return bool|int
     */
    public function store($data, $isReturnedId = false)
    {
        if ($data['is_default'] === '1') {
            parent::where('is_default', 1)->update(['is_default' => 0]);
        }

        if ($id = parent::insertGetId($data)) {
            $fileIds = [];
            if (request()->has('file_id')) {
                foreach (request()->file_id as $data) {
                    $fileIds[] = $data;
                }
            }
            ObjectFile::storeInObjectFiles($this->objectType(), $this->objectId(), $fileIds);

            return $isReturnedId == true ? $id : true;
        }

        return false;
    }

    /**
     * Update an existing chat bot.
     *
     * @param  array  $data
     * @param  int    $id
     * @return bool
     */
    public function updateBot($data = [], $id = null)
    {
        $result = $this->where('id', $id);
        if ($result->exists()) {
            if ($data['is_default'] === '1') {
                parent::where('is_default', 1)->update(['is_default' => 0]);
            }
            if ($result->update($data)) {
                if (request()->file_id) {
                    $result->first()->updateFiles(['isUploaded' => false, 'isOriginalNameRequired' => true, 'thumbnail' => true]);
                    return true;
                } else {
                    return $result->first()->deleteFromMediaManager();
                }
            }
        }

        return false;
    }

    /**
     * Define a relation with the ChatCategory model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chatCategory()
    {
        return $this->belongsTo(ChatCategory::class, 'chat_category_id');
    }

    /**
     * Define a relation with the Chat model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'bot_id');
    }
}
