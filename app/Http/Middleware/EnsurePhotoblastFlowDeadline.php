<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhotoblastFlowDeadline
{
    public function handle(Request $request, Closure $next)
    {
        $deadline = $request->session()->get('pb_flow_deadline');

        if ($deadline !== null) {
            $deadlineTs = (int) $deadline;
            if ($deadlineTs > 0 && time() > $deadlineTs) {
                // Timeout: hard reset session (new session ID) so next user starts clean
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('pb.timeout');
            }
        }

        return $next($request);
    }
}
