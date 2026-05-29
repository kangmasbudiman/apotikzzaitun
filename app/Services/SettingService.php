<?php 

namespace App\Services;

use App\Models\Site;

class SettingService
{

    public function store(array $data, object $logo = null): Void
    {
        if ($logo) {
            $data['logo'] = $this->uploadLogo($logo);
        }

        Site::first()->update($data);
    }

    public function uploadLogo(object $logo): String
    {
        $extension = pathinfo($logo->getClientOriginalName(), PATHINFO_EXTENSION);
        $time = time();

        $name = $time.'.'.$extension;

        $logo->storeAs('/public/logo', $name);

        return $name;
    }

}

 ?>