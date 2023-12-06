<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vlop' => $this->vlop,
            'dsa_common_id' => $this->dsa_common_id,
            'form_statements' => $this->form_statements_count,
            'api_statements' => $this->api_statements_count,
            'api_multi_statements' => $this->api_multi_statements_count,

        ];
    }
}
