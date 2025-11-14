<?php

namespace App\Services;

use App\Models\Type;

class TypeService
{
    /**
     * Get all types.
     */
    public function getAll()
    {
        return Type::orderBy('id', 'desc')->get();
    }

    /**
     * Get one type by id.
     */
    public function getOne($id)
    {
        return Type::findOrFail($id);
    }

    /**
     * Create a new type.
     */
    public function create(array $data)
    {
        return Type::create($data);
    }

    /**
     * Update a type.
     */
    public function update($id, array $data)
    {
        $type = Type::findOrFail($id);
        $type->update($data);
        return $type;
    }

    /**
     * Delete a type.
     */
    public function delete($id)
    {
        $type = Type::findOrFail($id);
        return $type->delete();
    }
}

