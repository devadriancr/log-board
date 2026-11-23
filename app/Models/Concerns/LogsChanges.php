<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Guarda en la bitácora quién creó, editó o eliminó el registro.
 *
 * El modelo que lo usa define:
 *  - $logSection: nombre de la sección (boards, cards, links...)
 *  - $logFields: campos cuyo cambio se guarda
 *  - activityLabel(): texto con el que se reconoce el registro (aunque ya no exista)
 */
trait LogsChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logSection)
            ->logOnly($this->logFields)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn () => (string) $this->activityLabel());
    }
}
