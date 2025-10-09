<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillUserId extends Command
{
    protected $signature = 'tenant:backfill {userId?}';
    protected $description = 'Preenche user_id onde estiver NULL nas tabelas do domínio';

    public function handle(): int
    {
        $userId = (int)($this->argument('userId') ?? env('USER_ID_PADRAO', 3));
        $tables = [
            'clientes','veiculos','estoques','servicos','ordens_servico',
            'pecas_ordem','servicos_ordem','cliente_veiculo',
        ];

        $this->info("Usando user_id={$userId}");
        foreach ($tables as $t) {
            if (!DB::getSchemaBuilder()->hasColumn($t, 'user_id')) continue;
            $affected = DB::table($t)->whereNull('user_id')->update(['user_id' => $userId]);
            $this->line("{$t}: {$affected} linha(s) atualizada(s).");
        }
        $this->info('OK');
        return self::SUCCESS;
    }
}
