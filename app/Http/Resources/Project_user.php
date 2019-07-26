<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Project_user extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'desc' => $this->desc,
            'deadline' => $this->deadline,
            'active' => $this->active,
            'currently_assigned' => $this->pivot->currently_assigned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'self' => url("/api/projects/{$this->id}"),
            'users' => url("/api/projects/{$this->id}/users")
        ];
    }
}
