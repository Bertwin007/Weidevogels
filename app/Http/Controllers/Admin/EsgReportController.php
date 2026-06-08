<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EsgBewijsReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EsgReportController extends Controller
{
    public function __construct(
        private readonly EsgBewijsReportService $reports,
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
        ]);
    }

    public function pdf(Request $request, string $partnerSlug): Response
    {
        $company = $this->resolveCompanyOrFail($partnerSlug);
        $season = (int) $request->integer('season', (int) now()->year);
        $data = $this->reports->build($company, $season);

        $filename = sprintf('biodiversiteits-bewijs-%s-%d.pdf', $partnerSlug, $season);

        return Pdf::loadView('reports.bewijs-rapport', [
            'data' => $data,
            'preview' => false,
        ])
            ->setPaper('a4')
            ->download($filename);
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
