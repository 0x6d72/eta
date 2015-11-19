<?php

require '../src/etaBnf.php';
require '../src/etaCompiler.php';
require '../src/etaOpcode.php';
require '../src/etaCompilerLangEta.php';
require '../src/etaVm.php';
require '../src/etaAux.php';

class io implements etaVmIo
{
	public function in()
	{
	}

	public function out($mValue)
	{
		echo $mValue;
	}

	public function control($mControlValue)
	{
	}
}

$sSrcCode = <<<EOT

getGreeting ()
{
	local t = []

	t['h'] = 'hello'
	t['w'] = 'world'

	return t
}

buildGreeting (t)
{
	local r = ''

	foreach k, v in t 
	{
		r = r ~ k ~ ': ' ~ v ~ '\n'
	}

	return r
}

greeting()
{
	return buildGreeting(getGreeting())
}

main ()
{
	write greeting()
}

main()

EOT;

try
{
	$oCompiler = new etaCompiler(
		new etaCompilerLangEta,
		new etaCompilerSrcCodeReaderString($sSrcCode)
	);

	$oContext = $oCompiler->compile();

	// $oMemory = new etaVmMemory;
	// $oAssembler = new etaCompilerVmMemoryAssembler($oMemory);
	// $oAssembler->assemble($oContext);

	$oByteCodeWriter = new etaCompilerByteCodeWriterString;
	$oAssembler = new etaCompilerByteCodeAssembler($oByteCodeWriter);
	$oAssembler->assemble($oContext);
	file_put_contents('test.ceta', $oByteCodeWriter->getByteCode());

	$oDisassembler = new etaVmBytecodeDisassembler;
	$oMemory = $oDisassembler->disassemble(new etaVmByteCodeReaderString($oByteCodeWriter->getByteCode()));
	
	$oVm = new etaVm($oMemory, new io);
	$oVm->exec();
	
	//etaAuxMemoryDump::dump($oMemory);
}
catch(Exception $oError)
{
	echo $oError->getMessage();
	echo PHP_EOL;
}

?>