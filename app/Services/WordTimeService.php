<?php

namespace App\Services;

use App\Models\WordTime;

class WordTimeService
{
    /**
     * Get all word times.
     */
    public function getAll()
    {
        return WordTime::orderBy('time', 'asc')->get();
    }

    /**
     * Get one word time by id.
     */
    public function getOne($id)
    {
        return WordTime::findOrFail($id);
    }

    /**
     * Create a new word time.
     */
    public function create(array $data)
    {
        return WordTime::create($data);
    }

    /**
     * Update a word time.
     */
    public function update($id, array $data)
    {
        $wordTime = WordTime::findOrFail($id);
        $wordTime->update($data);
        return $wordTime;
    }

    /**
     * Delete a word time.
     */
    public function delete($id)
    {
        $wordTime = WordTime::findOrFail($id);
        return $wordTime->delete();
    }
}

