<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTopic;
use App\Models\AuditEvaluation;
use App\Models\ActivityLog;

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
}
