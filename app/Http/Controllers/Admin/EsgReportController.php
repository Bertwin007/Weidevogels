<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EsgBewijsReportMail;
use App\Services\EsgBewijsReportService;
use App\Services\EsgReportDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EsgReportController extends Controller
{
    public function __construct(
        private readonly EsgBewijsReportService $reports,
        private readonly EsgReportDeliveryService $deliveryLog,
    ) {}

    public function index(Request $request): View
    {
        $season = (int) $request->integer('season', (int) now()->year);

        return view('admin.esg-reports.index', [
            'season' => $season,
            'partners' => $this->reports->listPartners($season),
            'seasonOptions' => $this->seasonOptions(),
        ]);
    }

    public function show(Request $request, string $partnerSlug): View
    {
        $company = $this->resolveCompanyOrFail($partnerSlug);
        $season = (int) $request->integer('season', (int) now()->year);
        $data = $this->reports->build($company, $season);

        return view('reports.bewijs-rapport', [
            'data' => $data,
            'preview' => true,
            'partnerEmail' => $this->reports->partnerEmail($company),
            'partnerSlug' => $partnerSlug,
        ]);
    }

    public function pdf(Request $request, string $partnerSlug): Response
    {
        $company = $this->resolveCompanyOrFail($partnerSlug);
        $season = (int) $request->integer('season', (int) now()->year);
        $data = $this->reports->build($company, $season);

        return $this->reports->renderPdf($data)
            ->download($this->reports->pdfFilename($partnerSlug, $season));
    }

    public function send(Request $request, string $partnerSlug): RedirectResponse
    {
        $company = $this->resolveCompanyOrFail($partnerSlug);
        $season = (int) $request->integer('season', (int) now()->year);

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'season' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $email = $validated['email'] ?? $this->reports->partnerEmail($company);

        if (! filled($email)) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Geen e-mailadres bekend. Vul een adres in of stel guest_email in bij een inzending.',
                ]);
        }

        $data = $this->reports->build($company, $season);
        $filename = $this->reports->pdfFilename($partnerSlug, $season);
        $pdfBinary = $this->reports->renderPdf($data)->output();

        Mail::to($email)->send(new EsgBewijsReportMail($data, $pdfBinary, $filename));

        $this->deliveryLog->log($company, $email, $data, (int) auth()->id());

        return redirect()
            ->route('admin.esg-reports.index', ['season' => $season])
            ->with('success', "Rapport {$data['report']['nr']} verstuurd naar {$email}.");
    }

    private function resolveCompanyOrFail(string $partnerSlug): string
    {
        $company = $this->reports->resolveCompany($partnerSlug);

        if ($company === null) {
            abort(404, 'Partner niet gevonden.');
        }

        return $company;
    }

    /**
     * @return list<int>
     */
    private function seasonOptions(): array
    {
        $current = (int) now()->year;

        return range($current, $current - 4);
    }
}
