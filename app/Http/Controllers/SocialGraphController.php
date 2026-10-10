<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Connection;
use App\Models\User;
use App\Models\UserBlock;
use App\Notifications\ConnectionRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialGraphController extends Controller
{
    public function toggleUserConnection(Request $request, int $user)
    {
        abort_if($user === auth()->id(), 422, 'You cannot connect with yourself.');

        $target = User::findOrFail($user);
        abort_if($target->isSuspended(), 422, 'This account is not available.');
        $this->abortIfBlocked($user);

        $created = DB::transaction(function () use ($user, $target) {
            $connection = Connection::where('user_id', auth()->id())
                ->where('target_user_id', $user)
                ->where('type', 'connection')
                ->first();

            if ($connection) {
                $connection->delete();

                return false;
            }

            $connection = Connection::firstOrCreate([
                'user_id' => auth()->id(),
                'target_user_id' => $user,
                'type' => 'connection',
            ], ['status' => 'accepted']);

            $target->notify(new ConnectionRequested(auth()->user()));
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'connection.created', 'auditable_type' => Connection::class, 'auditable_id' => $connection->id, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);

            return true;
        });

        return back()->with('success', $created ? __('flash.connection_created') : __('flash.connection_removed'));
    }

    public function toggleBlock(Request $request, int $user)
    {
        abort_if($user === auth()->id(), 422, 'You cannot block yourself.');
        User::findOrFail($user);

        $blocked = DB::transaction(function () use ($user) {
            $block = UserBlock::where('user_id', auth()->id())
                ->where('blocked_user_id', $user)
                ->first();

            if ($block) {
                $block->delete();

                return false;
            }

            UserBlock::firstOrCreate([
                'user_id' => auth()->id(),
                'blocked_user_id' => $user,
            ]);

            Connection::where(function ($q) use ($user) {
                $q->where('user_id', auth()->id())->where('target_user_id', $user);
            })->orWhere(function ($q) use ($user) {
                $q->where('user_id', $user)->where('target_user_id', auth()->id());
            })->delete();

            return true;
        });

        return back()->with('success', $blocked ? __('flash.user_blocked') : __('flash.user_unblocked'));
    }

    private function abortIfBlocked(int $otherUserId): void
    {
        $blocked = UserBlock::where(function ($query) use ($otherUserId) {
            $query->where('user_id', auth()->id())->where('blocked_user_id', $otherUserId);
        })->orWhere(function ($query) use ($otherUserId) {
            $query->where('user_id', $otherUserId)->where('blocked_user_id', auth()->id());
        })->exists();

        abort_if($blocked, 403, 'This interaction is not available.');
    }
}
