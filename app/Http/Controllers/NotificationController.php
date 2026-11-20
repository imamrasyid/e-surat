<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePushSubscriptionRequest;
use App\Http\Requests\StorePushSubscriptionRequest;
use App\Services\NotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationCenter $center) {}

    public function index(Request $request): Response
    {
        return Inertia::render('notifications/Index', [
            'breadcrumbItems' => [
                [
                    'title' => 'Pemberitahuan',
                ],
            ],
            'notifications' => fn () => $this->center->history($request->user()),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $row = $this->center->findOwned($request->user(), $notification);

        if ($row->read_at === null) {
            $row->markAsRead();
        }

        return back();
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $this->center->findOwned($request->user(), $notification)->delete();

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return back();
    }

    public function storePushSubscription(StorePushSubscriptionRequest $request): RedirectResponse
    {
        abort_unless($this->center->webPushProps()['enabled'], 404);

        $request->user()->updatePushSubscription(
            $request->string('endpoint')->toString(),
            $request->string('keys.p256dh')->toString(),
            $request->string('keys.auth')->toString(),
            filled($request->input('content_encoding')) ? (string) $request->input('content_encoding') : null,
        );

        return back();
    }

    public function destroyPushSubscription(DeletePushSubscriptionRequest $request): RedirectResponse
    {
        abort_unless($this->center->webPushProps()['enabled'], 404);

        $request->user()->deletePushSubscription($request->string('endpoint')->toString());

        return back();
    }
}
