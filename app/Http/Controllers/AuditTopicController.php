<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTopic;
use App\Models\AuditEvaluation;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class AuditTopicController extends Controller
{
    /**
     * Get all audit topics and their evaluations.
     */
    public function index()
    {
        $topics = AuditTopic::with('evaluations')->get();
        return response()->json($topics);
    }

    /**
     * Create a new audit topic.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_kerja' => 'required|string',
            'name' => 'required|string',
            'kriteria' => 'nullable|string',
        ]);

        $topic = AuditTopic::create($request->all());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_TOPIC_CREATED',
            'description' => "Membuat topik audit baru '{$topic->name}' untuk Unit Kerja {$topic->unit_kerja}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($topic, 201);
    }

    /**
     * Update an audit topic.
     */
    public function update(Request $request, $id)
    {
        $topic = AuditTopic::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'kriteria' => 'nullable|string',
        ]);

        $topic->update($request->only('name', 'kriteria'));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_TOPIC_UPDATED',
            'description' => "Memperbarui topik audit '{$topic->name}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($topic);
    }

    /**
     * Delete an audit topic.
     */
    public function destroy(Request $request, $id)
    {
        $topic = AuditTopic::findOrFail($id);
        $topicName = $topic->name;
        $topic->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_TOPIC_DELETED',
            'description' => "Menghapus topik audit '{$topicName}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Topic deleted successfully']);
    }

    /**
     * Save or update an evaluation for a specific user and topic.
     */
    public function updateEvaluation(Request $request, $topicId, $userId)
    {
        $topic = AuditTopic::findOrFail($topicId);

        $request->validate([
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $evaluation = AuditEvaluation::updateOrCreate(
            ['audit_topic_id' => $topicId, 'user_id' => $userId],
            [
                'status' => $request->input('status', 'Perlu Penguatan'),
                'keterangan' => $request->input('keterangan', '')
            ]
        );

        return response()->json($evaluation);
    }

    /**
     * Get all topics in the format expected by frontend (penugasan-kompetensi).
     */
    public function getTopics()
    {
        $topics = AuditTopic::with('evaluations')->get()->map(function($topic) {
            return [
                'id' => $topic->id,
                'unit_kerja' => $topic->unit_kerja,
                'nama_penugasan' => $topic->name,
                'kriteria' => $topic->kriteria,
                'evaluations' => $topic->evaluations
            ];
        });
        return response()->json($topics);
    }

    /**
     * Sync topics for a specific unit_kerja.
     */
    public function syncTopics(Request $request)
    {
        $request->validate([
            'unit_kerja' => 'required|string',
            'penugasan_list' => 'array',
        ]);

        $unitKerja = $request->unit_kerja;
        $newList = $request->penugasan_list ?? [];

        DB::beginTransaction();
        try {
            // Current topics for this unit_kerja
            $existingTopics = AuditTopic::where('unit_kerja', $unitKerja)->get();
            $existingTopicIds = $existingTopics->pluck('id')->toArray();

            $newIds = [];

            foreach ($newList as $item) {
                if (!empty($item['id']) && is_numeric($item['id']) && in_array($item['id'], $existingTopicIds)) {
                    // Update
                    $topic = AuditTopic::find($item['id']);
                    $topic->update([
                        'name' => $item['nama_penugasan'] ?? $item['name'],
                        'kriteria' => $item['kriteria'] ?? null,
                    ]);
                    $newIds[] = $topic->id;
                } else {
                    // Create
                    $topic = AuditTopic::create([
                        'unit_kerja' => $unitKerja,
                        'name' => $item['nama_penugasan'] ?? $item['name'],
                        'kriteria' => $item['kriteria'] ?? null,
                    ]);
                    $newIds[] = $topic->id;
                }
            }

            // Delete those not in newIds
            $topicsToDelete = AuditTopic::where('unit_kerja', $unitKerja)->whereNotIn('id', $newIds)->get();
            foreach ($topicsToDelete as $td) {
                $td->delete();
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'event_type' => 'AUDIT_TOPIC_SYNCED',
                'description' => "Menyelaraskan topik audit untuk Unit Kerja {$unitKerja}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();
            return response()->json(['message' => 'Topics synced successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan penugasan', 'details' => $e->getMessage()], 500);
        }
    }
}
