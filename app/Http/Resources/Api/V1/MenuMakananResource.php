<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MenuMakanan;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MenuMakanan */
class MenuMakananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentVote = $this->relationLoaded('votes')
            ? $this->votes->first()?->vote_type
            : null;

        $likeCount = $this->likes_count
            ?? ($this->relationLoaded('votes') ? $this->votes->where('vote_type', 'like')->count() : 0);
        $dislikeCount = $this->dislikes_count
            ?? ($this->relationLoaded('votes') ? $this->votes->where('vote_type', 'dislike')->count() : 0);

        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'menu' => $this->menu,
            'nutrition_info' => $this->nutrition_info,
            'photo' => ApiMedia::fromPath($this->photo),
            'photo_kegiatan' => ApiMedia::fromPath($this->photo_kegiatan),
            'vote' => [
                'type' => $currentVote,
                'like_count' => (int) $likeCount,
                'dislike_count' => (int) $dislikeCount,
            ],
        ];
    }
}
