<?php

require '../src/etaBnf.php';
require '../src/etaCompiler.php';
require '../src/etaCompilerLangAsm.php';
require '../src/etaOpcode.php';
require '../src/etaVm.php';
require '../src/etaAux.php';

class io implements etaVmIo
{
	public function in()
	{
	}

	public function out($mValue)
	{
		var_dump($mValue);
	}

	public function control($mControlValue)
	{
	}
}

$sSrcCode = <<<EOT

		push	#func
		call	#0
		pop		#1
		hlt

func	out 	"calculating 10 + 20"
		push	#10
		push	#20
		add
		out		-1
		pushnil
		ret

EOT;

try
{
	$oMemory = new etaVmMemory;

	$oCompiler = new etaCompiler(
		new etaCompilerLangAsm,
		new etaCompilerSrcCodeReaderString($sSrcCode)
	);

	$oContext = $oCompiler->compile();

	$oAssembler = new etaCompilerVmMemoryAssembler($oMemory);
	$oAssembler->assemble($oContext);

	$oVm = new etaVm($oMemory, new io);
	$oVm->exec();
	
	etaAuxMemoryDump::dump($oMemory);
}
catch(Exception $oError)
{
	echo $oError->getMessage();
	echo PHP_EOL;
}

?>