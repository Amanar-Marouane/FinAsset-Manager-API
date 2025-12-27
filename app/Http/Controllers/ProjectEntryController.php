<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectEntryResource;
use App\Models\ProjectEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Unk\LaravelApiResponse\Traits\{HttpResponse};

class ProjectEntryController extends Controller
{
    use HttpResponse;


    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $entry = DB::table('project_entries')->updateOrInsert(
            [
                'project_id' => $validated['project_id'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ],
            [
                'amount' => $validated['amount'],
                'updated_at' => now(),
            ]
        );

        if (!$entry) {
            return $this->error('Échec de la création ou de la mise à jour de l\'entrée du projet.', 500);
        }

        $projectEntry = ProjectEntry::where('project_id', $validated['project_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        return $this->success(
            new ProjectEntryResource($projectEntry),
            'Entrée de projet créée avec succès.',
            201
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $projectEntry = ProjectEntry::where('project_id', $validated['project_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        $projectEntry->delete();

        return $this->success(null, 'L\'entrée de projet supprimée avec succès.');
    }
}
