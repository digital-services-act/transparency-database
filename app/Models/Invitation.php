<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory, SoftDeletes;

//    public function acceptUser(User $user): bool
//    {
//        if ($user->email !== $this->email) return false;
//
//        // Link user to the platform
//        $user->platform = $this->platform;
//        $user->save();
//
//        // Give Contributor rights to the user
//        $user->assignRole('Contributor');
//
//        // Delete the invitation
//        $this->delete();
//
//        return true;
//
//
//
//    }
}
