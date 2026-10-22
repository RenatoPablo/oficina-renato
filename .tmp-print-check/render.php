<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array']);
$user=new App\Models\User; $user->forceFill(['id'=>7,'is_admin'=>false,'name'=>'Teste']); auth()->setUser($user);
$os=new App\Models\OrdemServico; $os->forceFill(['id'=>3,'situacao'=>'Finalizada','data_chamado'=>'2026-10-22 10:00:00','cliente_nome_snapshot'=>'Cliente de teste','problema_reclamado'=>'Barulho na direção','frete'=>0,'total_servicos'=>300,'total_pecas'=>730,'total_os'=>1030]);
$veiculo=new App\Models\Veiculo; $veiculo->forceFill(['placa'=>'ABC1234','marca'=>'Volkswagen','modelo'=>'Gol','ano'=>2010]);
$os->setRelation('veiculo',$veiculo);$os->setRelation('cliente',null);
$serv=new App\Models\ServicoOrdem; $serv->forceFill(['qtd'=>1,'valor_unit'=>300,'valor_total'=>300]);$serv->setRelation('servico',(new App\Models\Servico)->forceFill(['descricao'=>'Serviço de mão de obra']));
$os->setRelation('servicosItens',collect([$serv]));
$pecas=collect(); foreach([['Flexível de freio',1,660],['Óleo hidráulico',2,35]] as [$desc,$q,$v]) {$p=new App\Models\PecaOrdem;$p->forceFill(['qtd'=>$q,'valor_unit'=>$v,'valor_total'=>$q*$v]);$p->setRelation('estoque',(new App\Models\Estoque)->forceFill(['descricao'=>$desc]));$pecas->push($p);}
$os->setRelation('pecasItens',$pecas);
if (($argv[1] ?? '') === 'longa') { $os->setRelation('pecasItens', collect(array_fill(0, 80, $pecas->first()))); }
$html=view('ordens.show',['os'=>$os,'errors'=>new Illuminate\Support\ViewErrorBag])->render();
file_put_contents(__DIR__.'/nota.html',$html);
echo 'inline style count: '.substr_count($html,'os-document-header {').PHP_EOL;
