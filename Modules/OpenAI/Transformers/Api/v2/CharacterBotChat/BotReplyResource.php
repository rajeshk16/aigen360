<?php

namespace Modules\OpenAI\Transformers\Api\v2\CharacterBotChat;

use Illuminate\Http\Resources\Json\JsonResource;

class BotReplyResource extends JsonResource
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
            'chat_id' => $this->parent_id,
            'provider' => $this->provider,
            'expense' => $this->expense,
            'expense_type' => $this->expense_type,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'meta' => $this->metas->pluck('value', 'key'),
            'bot_details' => new ChatBotResource($this->whenLoaded('chatbot')),
        ];
    }
}
