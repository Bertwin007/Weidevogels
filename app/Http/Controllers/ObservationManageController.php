<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ObservationManageController extends Controller
{
    public function destroy(Request $request, Observation $observation): RedirectResponse
    {
        $this->authorize('delete', $observation);

        $wasPublished = $observation->isPublished();
        $observation->purge();

        $redirect = $wasPublished
            ? route('moments.index')
            : route('annotate.index');

        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/admin')) {
            $redirect = route('admin.dashboard');
        }

        return redirect($redirect)->with('success', 'Moment definitief verwijderd.');
    }
}
