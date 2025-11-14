<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * Get all settings.
     */
    public function getAll()
    {
        return Setting::all();
    }

    /**
     * Get first setting (usually only one).
     */
    public function getFirst()
    {
        return Setting::orderBy('id')->first();
    }

    /**
     * Get one setting by id.
     */
    public function getOne($id)
    {
        return Setting::findOrFail($id);
    }

    /**
     * Create a new setting.
     */
    public function create(array $data)
    {
        return Setting::create($data);
    }

    /**
     * Update a setting.
     */
    public function update($id, array $data)
    {
        $setting = Setting::findOrFail($id);
        $setting->update($data);
        return $setting;
    }

    /**
     * Delete a setting.
     */
    public function delete($id)
    {
        $setting = Setting::findOrFail($id);
        return $setting->delete();
    }
}

