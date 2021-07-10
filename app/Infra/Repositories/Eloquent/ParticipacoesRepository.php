<?php

namespace App\Infra\Repositories\Eloquent;

use Core\Models\Participacao;
use App\Models\Participacao as M;

class ParticipacoesRepository implements \Core\Contracts\Repositories\IParticipacoesRepository
{
    public static function e2m(Participacao $e)
    {
        $m = new M();
        if(!is_null($e->id)){
            $m->id = $e->id;
        }
        $m->id_corredor = $e->corredor->id;
        $m->id_prova = $e->prova->id;
        $m->horarioInicio = $e->horarioInicio->format('H:i') ?? null;
        $m->horarioFim = $e->horarioFim->format('H:i') ?? null;
        return $m;
    }

    public static function m2e(?M $m): ?Prova
    {
        if(is_null($m)) {
            return null;
        }

        $e = new Prova(
            $m->id,
            CorredoresRepository::m2e($m->corredor),
            ProvaRepository::m2e($m->prova)
        );

        $e->horarioInicio = date_create_from_format('H:i', $m->horarioInicio);
        $e->horarioFim = date_create_from_format('H:i', $m->horarioFim);
        return $e;
    }

    public function save(Participacao $e): Participacao
    {
        $m = self::e2m($e);
        $m->save();
        $e->id = $m->id;
        return $e;
    }

    public function findById(int $id): ?Participacao
    {
        $m = M::find($id);
        return self::m2e($m);
    }

    public function findByCorredorProva(int $c, int $p): ?Participacao
    {
        $m = M::where('id_corredor', $c)
            ->where('id_prova', $p)
            ->first();
        return self::m2e($m);
    }

    public function possuiParticipacaoNoDia(\DateTime $dia): bool
    {
        $participacoes = M::whereHas('prova', function ($query) {
            return $query->whereDate('data', '=', $dia->format('Y-m-d'));
        })->get();

        return empty($participacoes);
    }
}
