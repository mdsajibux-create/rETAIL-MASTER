<?php

namespace Modules\Chat\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoForChatResource extends JsonResource
{

    protected string $userType;

    public function __construct($resource, string $userType)
    {
        parent::__construct($resource);
        $this->userType = $userType;
    }

     public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => in_array($this->userType, ['admin', 'deliveryman', 'customer']) ? $this->full_name : ($this->name ?? ''),
            'phone'    => $this->phone,
            'email'    => $this->email,
            'activity_scope'    => $this->activity_scope,
            'image'    => ImageModifier::generateImageUrl($this->image),
            'is_online'    => $this->online_at ? \Carbon\Carbon::parse($this->online_at)->gt(now()->subMinutes(3)) : false,
        ];
    }
}
