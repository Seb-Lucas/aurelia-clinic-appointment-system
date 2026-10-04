<?php

namespace App\Services;

use App\Core\Database;

class AuditLogService
{
    public function log(int $actorId, string $action, string $targetType, int $targetId, string $result, array $metadata = []): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action, target_type, target_id, result, metadata, created_at) VALUES (:actor_id, :action, :target_type, :target_id, :result, :metadata, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'result' => $result,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }
}
