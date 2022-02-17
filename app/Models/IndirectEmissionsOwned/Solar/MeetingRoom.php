<?php

namespace App\Models\IndirectEmissionsOwned\Solar;

use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    const TABLE_NAME = 'meeting_rooms';

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
