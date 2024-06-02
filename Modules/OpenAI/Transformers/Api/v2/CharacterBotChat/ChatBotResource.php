<?php

namespace Modules\OpenAI\Transformers\Api\v2\CharacterBotChat;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatBotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'chat_category_id' => $this->chat_category_id,
            'name' => $this->name,
            'code' => $this->code,
            'message' => $this->message,
            'role' => $this->role,
            'prompt' => $this->promt,
            'image_url' => $this->fileUrl(),
            'is_default' => $this->is_default,
            'bot_plan' => $this->bot_plan,
            'status' => $this->status,
            'chat_category' => [
                'id' => $this->chatCategory->id,
                'name' => $this->chatCategory->name,
                'slug' => $this->chatCategory->slug,
                'description' => $this->chatCategory->description,
            ]
        ];
    }
}
