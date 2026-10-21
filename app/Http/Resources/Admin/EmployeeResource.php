<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'profile' => $this->whenLoaded('employeeProfile', fn () => [
                'employee_id' => $this->employeeProfile?->employee_id,
                'department' => $this->employeeProfile?->department,
                'designation' => $this->employeeProfile?->designation,
                'is_active' => $this->employeeProfile?->is_active,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
