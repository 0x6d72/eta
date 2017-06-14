<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017 0x6d72 <0x6d72@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

abstract class etaCompilerRefPoint
{
	/**
	 * @var string
	 */
	protected $sName;

	/**
	 * @param string $sName
	 * @return void
	 */
	public function __construct($sName)
	{
		$this->sName = $sName;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->sName;
	}
}

class etaCompilerRefPointProxy extends etaCompilerRefPoint
{
	/**
	 * @var etaCompilerRefPoint
	 */
	protected $oRefPoint;

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @return void
	 */
	public function setRefPoint(etaCompilerRefPoint $oRefPoint)
	{
		$this->oRefPoint = $oRefPoint;
	}

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @return void
	 */
	public function setRefPointIfEmpty(etaCompilerRefPoint $oRefPoint)
	{
		if($this->isEmpty())
		{
			$this->oRefPoint = $oRefPoint;
		}
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint()
	{
		return $this->oRefPoint;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return !($this->oRefPoint instanceof etaCompilerRefPoint);
	}
}

class etaCompilerRefPointInst extends etaCompilerRefPoint
{
	/**
	 * @var int
	 */
	protected $iInst;

	/**
	 * @param string $sName
	 * @param int $iInst
	 * @return void
	 */
	public function __construct($sName, $iInst)
	{
		parent::__construct($sName);

		$this->iInst = $iInst;
	}

	/**
	 * @param int $iInst
	 * @return void
	 */
	public function setInst($iInst)
	{
		$this->iInst = $iInst;
	}

	/**
	 * @return int
	 */
	public function getInst()
	{
		return $this->iInst;
	}
}

class etaCompilerRefPointSlot extends etaCompilerRefPoint
{
	/**
	 * @var int
	 */
	protected $iSlot;

	/**
	 * @param string $sName
	 * @param int $iSlot
	 * @return void
	 */
	public function __construct($sName, $iSlot)
	{
		parent::__construct($sName);

		$this->iSlot = $iSlot;
	}

	/**
	 * @return int
	 */
	public function getSlot()
	{
		return $this->iSlot;
	}
}

abstract class etaCompilerAddr
{
}

class etaCompilerAddrNone extends etaCompilerAddr
{
}

class etaCompilerAddrType extends etaCompilerAddr
{
	/**
	 * @var bool
	 */
	protected $bIndirect;

	/**
	 * @var bool
	 */
	protected $bRelative;

	/**
	 * @param bool $bIndirect
	 * @param bool $bRelative
	 * @return void
	 */
	public function __construct($bIndirect, $bRelative)
	{
		$this->bIndirect = $bIndirect;

		$this->bRelative = $bRelative;
	}

	/**
	 * @return bool
	 */
	public function isIndirect()
	{
		return $this->bIndirect;
	}

	/**
	 * @param bool $bIndirect
	 * @return void
	 */
	public function setIsIndirect($bIndirect)
	{
		$this->bIndirect = $bIndirect;
	}

	/**
	 * @return bool
	 */
	public function isRelative()
	{
		return $this->bRelative;
	}

	/**
	 * @param bool $bRelative
	 * @return void
	 */
	public function setIsRelative($bRelative)
	{
		$this->bRelative = $bRelative;
	}
}

class etaCompilerAddrValue extends etaCompilerAddrType
{
	/**
	 * @var int
	 */
	protected $iVal;

	/**
	 * @param int $iVal
	 * @param bool $bIndirect
	 * @param bool $bRelative
	 * @return void
	 */
	public function __construct($iVal, $bIndirect, $bRelative)
	{
		parent::__construct($bIndirect, $bRelative);

		$this->iVal = $iVal;
	}

	/**
	 * @return int
	 */
	public function getVal()
	{
		return $this->iVal;
	}

	/**
	 * @param int $iVal
	 * @return void
	 */
	public function setVal($iVal)
	{
		$this->iVal = $iVal;
	}
}

class etaCompilerAddrDirect extends etaCompilerAddrValue
{
	/**
	 * @param int $iVal
	 * @return void
	 */
	public function __construct($iVal)
	{
		parent::__construct($iVal, false, false);
	}
}

class etaCompilerAddrIndirect extends etaCompilerAddrValue
{
	/**
	 * @param int $iVal
	 * @return void
	 */
	public function __construct($iVal)
	{
		parent::__construct($iVal, true, false);
	}
}

class etaCompilerAddrRefPoint extends etaCompilerAddrType
{
	/**
	 * @var etaCompilerRefPoint
	 */
	protected $oRefPoint;

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @param bool $bIndirect
	 * @param bool $bRelative
	 * @return void
	 */
	public function __construct(
		etaCompilerRefPoint $oRefPoint,
		$bIndirect,
		$bRelative = false
	)
	{
		parent::__construct($bIndirect, $bRelative);

		$this->oRefPoint = $oRefPoint;
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint()
	{
		return $this->oRefPoint;
	}

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 */
	public function setRefPoint(etaCompilerRefPoint $oRefPoint)
	{
		$this->oRefPoint = $oRefPoint;
	}
}

class etaCompilerInst
{
	/**
	 * @var int
	 */
	protected $iOpcode;

	/**
	 *
	 * @var etaCompilerAddr
	 */
	protected $oAddr;

	/**
	 * @param int $iOpcode
	 * @param etaCompilerAddr $oAddr
	 * @return void
	 */
	public function __construct($iOpcode, etaCompilerAddr $oAddr)
	{
		$this->iOpcode = $iOpcode;

		$this->oAddr = $oAddr;
	}

	/**
	 * @return int
	 */
	public function getOpcode()
	{
		return $this->iOpcode;
	}

	/**
	 * @return etaCompilerAddr
	 */
	public function getAddr()
	{
		return $this->oAddr;
	}

	/**
	 * @param etaCompilerAddr $oAddr
	 * @return void
	 */
	public function setAddr(etaCompilerAddr $oAddr)
	{
		$this->oAddr = $oAddr;
	}
}

class etaCompilerRefPointSet
{
	/**
	 * @var array
	 */
	protected $aRefPoints = array();

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @return int
	 */
	public function addRefPoint(etaCompilerRefPoint $oRefPoint)
	{
		$iIndex = count($this->aRefPoints);

		$this->aRefPoints[$iIndex] = $oRefPoint;

		return $iIndex;
	}

	/**
	 * @param int $iIndex
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint($iIndex)
	{
		if(isset($this->aRefPoints[$iIndex]))
		{
			return $this->aRefPoints[$iIndex];
		}

		throw new Exception(sprintf(
			'there is no reference point at index %d', $iIndex
		));
	}

	/**
	 * @return array
	 */
	public function getAllRefPoints()
	{
		return $this->aRefPoints;
	}
}

class etaCompilerRefPointPair extends etaCompilerRefPointSet
{
	/**
	 * @param etaCompilerRefPoint $oRefPoint1
	 * @param etaCompilerRefPoint $oRefPoint2
	 * @return void
	 */
	public function __construct(
		etaCompilerRefPoint $oRefPoint1,
		etaCompilerRefPoint $oRefPoint2
	)
	{
		$this->addRefPoint($oRefPoint1);
		$this->addRefPoint($oRefPoint2);
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint1()
	{
		return $this->getRefPoint(0);
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint2()
	{
		return $this->getRefPoint(1);
	}
}

class etaCompilerRefPointInstPair
{
	/**
	 * @var etaCompilerRefPoint
	 */
	protected $oRefPoint;

	/**
	 * @var etaCompilerInst
	 */
	protected $oInst;

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @param etaCompilerInst $oInst
	 * @return void
	 */
	public function __construct(
		etaCompilerRefPoint $oRefPoint,
		etaCompilerInst $oInst
	)
	{
		$this->oRefPoint = $oRefPoint;

		$this->oInst = $oInst;
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint()
	{
		return $this->oRefPoint;
	}

	/**
	 * @return etaCompilerInst
	 */
	public function getInst()
	{
		return $this->oInst;
	}
}

class etaCompilerContext
{
	/**
	 * @var etaBnfTree
	 */
	protected $oSyntaxTree = null;

	/**
	 * @var array
	 */
	protected $aRefPoints = array();

	/**
	 * @var array
	 */
	protected $aInstructions = array();

	/**
	 * @var array
	 */
	protected $aSlots = array();

	/**
	 * @param etaBnfTree $oSyntaxTree
	 * @return void
	 */
	public function setSyntaxTree(etaBnfTree $oSyntaxTree)
	{
		$this->oSyntaxTree = $oSyntaxTree;
	}

	/**
	 * @return etaBnfTree
	 */
	public function getSyntaxTree()
	{
		if($this->oSyntaxTree instanceof etaBnfTree)
		{
			return $this->oSyntaxTree;
		}

		throw new Exception(
			'no syntax tree was assigned to this compiler context'
		);
	}

	/**
	 * @param string $sName
	 * @return bool
	 */
	public function hasRefPoint($sName)
	{
		return isset($this->aRefPoints[$sName]);
	}

	/**
	 * @param string $sName
	 * @return etaCompilerRefPoint
	 */
	public function getRefPoint($sName)
	{
		if($this->hasRefPoint($sName))
		{
			return $this->aRefPoints[$sName];
		}

		throw new Exception(sprintf(
			'there is no reference point with the name "%s"', $sName
		));
	}

	/**
	 * @param mixed $mValue
	 * @return etaCompilerRefPointSlot
	 */
	public function getRefPointVal($mValue)
	{
		$sName = sprintf('__val_%x', crc32(serialize($mValue)));

		try
		{
			return $this->getRefPoint($sName);
		}
		catch(Exception $oError)
		{
		}

		$iSlot = $this->addSlot($mValue);

		$oRefPoint = new etaCompilerRefPointSlot($sName, $iSlot);

		$this->addRefPoint($oRefPoint);

		return $oRefPoint;
	}

	/**
	 * @param string $sVar
	 * @return etaCompilerRefPointSlot
	 */
	public function getRefPointVar($sVar)
	{
		$sName = sprintf('__var_%s', $sVar);

		try
		{
			return $this->getRefPoint($sName);
		}
		catch(Exception $oError)
		{
		}

		$iSlot = $this->addSlot(null);

		$oRefPoint = new etaCompilerRefPointSlot($sName, $iSlot);

		$this->addRefPoint($oRefPoint);

		return $oRefPoint;
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getNextInstRefPoint()
	{
		$iNextInst = $this->getInstCount();

		$sName = sprintf('__inst_%d', $iNextInst);

		if(!$this->hasRefPoint($sName))
		{
			$oRefPoint = new etaCompilerRefPointInst($sName, $iNextInst);

			$this->addRefPoint($oRefPoint);
		}
		else
		{
			$oRefPoint = $this->getRefPoint($sName);
		}

		return $oRefPoint;
	}

	/**
	 * @return etaCompilerRefPointProxy
	 */
	public function getRefPointProxy()
	{
		$oProxy = new etaCompilerRefPointProxy(uniqid(microtime()));

		$this->addRefPoint($oProxy);

		return $oProxy;
	}

	/**
	 * @param etaCompilerRefPoint $oRefPoint
	 * @return void
	 */
	public function addRefPoint(etaCompilerRefPoint $oRefPoint)
	{
		if($this->hasRefPoint($oRefPoint->getName()))
		{
			throw new Exception(sprintf(
				'cannot overwrite reference point with the name "%s"',
				$oRefPoint->getName()
			));
		}

		$this->aRefPoints[$oRefPoint->getName()] = $oRefPoint;
	}

	/**
	 * @param etaCompilerInst $oInst
	 * @return int
	 */
	public function addInst(etaCompilerInst $oInst)
	{
		$iIndex = count($this->aInstructions);

		$this->aInstructions[$iIndex] = $oInst;

		return $iIndex;
	}

	/**
	 * @param etaCompilerInst $oInst
	 * @return etaCompilerRefPoint
	 */
	public function addInstRefPoint(etaCompilerInst $oInst)
	{
		$iInst = $this->addInst($oInst);

		$sName = sprintf('__inst_%d', $iInst);

		if($this->hasRefPoint($sName))
		{
			$oRefPoint = $this->getRefPoint($sName);
		}
		else
		{
			$oRefPoint = new etaCompilerRefPointInst($sName, $iInst);

			$this->addRefPoint($oRefPoint);
		}

		return $oRefPoint;
	}

	/**
	 * @return array
	 */
	public function getAllInst()
	{
		return $this->aInstructions;
	}

	/**
	 * @return int
	 */
	public function getInstCount()
	{
		return count($this->aInstructions);
	}

	/**
	 * @param mixed $mValue
	 * @return int
	 */
	public function addSlot($mValue)
	{
		$iSlot = count($this->aSlots);

		$this->aSlots[$iSlot] = $mValue;

		return $iSlot;
	}

	/**
	 * @return array
	 */
	public function getSlots()
	{
		return $this->aSlots;
	}

	/**
	 * @return int
	 */
	public function getSlotCount()
	{
		return count($this->aSlots);
	}
}

abstract class etaCompilerAssembler
{
	/**
	 * @param etaCompilerContext $oContext
	 * @return void
	 */
	abstract public function assemble(etaCompilerContext $oContext);

	/**
	 * @param etaCompilerAddr $oAddr
	 * @return array
	 */
	protected function getAddrData(etaCompilerAddr $oAddr, $iSlotOffset)
	{
		$bHasAddr = false;
		$bIndirect = false;
		$bRelative = false;
		$iAddr = 0;

		if($oAddr instanceof etaCompilerAddrType)
		{
			$bHasAddr = true;
			$bIndirect = $oAddr->isIndirect();
			$bRelative = $oAddr->isRelative();

			if($oAddr instanceof etaCompilerAddrValue)
			{
				$iAddr = $oAddr->getVal();
			}
			elseif($oAddr instanceof etaCompilerAddrRefPoint)
			{
				$oRefPoint = $oAddr->getRefPoint();

				while($oRefPoint instanceof etaCompilerRefPointProxy)
				{
					$oRefPoint = $oRefPoint->getRefPoint();
				}

				if($oRefPoint instanceof etaCompilerRefPointInst)
				{
					$iAddr = $oRefPoint->getInst();
				}
				elseif($oRefPoint instanceof etaCompilerRefPointSlot)
				{
					$iAddr = $oRefPoint->getSlot() + $iSlotOffset;
				}
			}
		}

		return array($bHasAddr, $bIndirect, $bRelative, $iAddr);
	}
}

interface etaCompilerByteCodeWriter
{
	/**
	 * @param string $sByteCode
	 * @return void
	 */
	public function writeByteCode($sByteCode);
}

class etaCompilerByteCodeWriterString implements etaCompilerByteCodeWriter
{
	/**
	 * "var string
	 */
	protected $sByteCode = '';

	/**
	 * @see etaCompilerByteCodeWriter::writeByteCode()
	 */
	public function writeByteCode($sByteCode)
	{
		$this->sByteCode = $sByteCode;
	}

	/**
	 * @return string
	 */
	public function getByteCode()
	{
		return $this->sByteCode;
	}
}

class etaCompilerByteCodeAssembler extends etaCompilerAssembler
{
	/**
	 *
	 * @var etaCompilerByteCodeWriter
	 */
	protected $oByteCodeWriter;

	/**
	 * @param etaCompilerByteCodeWriter $oByteCodeWriter
	 * @return void
	 */
	public function __construct(etaCompilerByteCodeWriter $oByteCodeWriter)
	{
		$this->oByteCodeWriter = $oByteCodeWriter;
	}

	/**
	 * @see etaCompilerAssembler::assemble()
	 */
	public function assemble(etaCompilerContext $oContext)
	{
		$iInstCount = $oContext->getInstCount();

		$aInstByteCodes = array();
		$aSlotByteCodes = array();

		foreach($oContext->getAllInst() as $oInst)
		{
			$aInstByteCodes[] = $this->genInstByteCode($oInst, $iInstCount);
		}

		foreach($oContext->getSlots() as $mSlotValue)
		{
			$sSlotByteCode = $this->genSlotByteCode($mSlotValue);

			if($sSlotByteCode !== null)
			{
				$aSlotByteCodes[] = $sSlotByteCode;
			}
		}

		$this->oByteCodeWriter->writeByteCode(sprintf(
			"%s%s%s%s",
			pack('C', 0xCC),
			pack('S', $iInstCount),
			implode('', $aInstByteCodes),
			implode('', $aSlotByteCodes)
		));
	}

	/**
	 * @param etaCompilerInst $oInst
	 * @param int $iSlotOffset
	 * @return string
	 */
	protected function genInstByteCode(etaCompilerInst $oInst, $iSlotOffset)
	{
		/**
		 * opcode     modifier
		 * 15     8   7      0
		 * OOOOOOOM   SARI-___
		 *
		 * O = opcode
		 * M = has modifier
		 * S = has short address
		 * A = has extra adress field
		 * R = has relative address
		 * I = has indirect address
		 * - = ultra short address sign
		 * _ = ultra short address
		 */

		list($bHasAddr, $bIndirect, $bRelative, $iAddr) = $this->getAddrData(
			$oInst->getAddr(), $iSlotOffset
		);

		$iOpcode = ($oInst->getOpcode() & 0x7F) << 1;
		$iMod = 0;

		$bHasMod = ($bHasAddr && $iAddr != 0) || $bIndirect || $bRelative;

		$bShort = false;

		if($bHasAddr)
		{
			if($this->isUltraShort($iAddr))
			{
				$bShort = true;
				$bHasAddr = false;

				if($iAddr >= 0)
				{
					$iMod |= $iAddr;
				}
				else
				{
					$iMod |= 0x08 | (-1 * $iAddr);
				}
			}
			else
			{
				$bShort = $this->isShort($iAddr);
			}
		}

		if($bHasMod)
		{
			$iOpcode |= 0x01;

			if($bIndirect)
			{
				$iMod |= 0x10;
			}

			if($bRelative)
			{
				$iMod |= 0x20;
			}

			if($bHasAddr)
			{
				$iMod |= 0x40;
			}

			if($bShort)
			{
				$iMod |= 0x80;
			}
		}

		return sprintf(
			'%s%s%s',
			pack('C', $iOpcode),
			$bHasMod ? pack('C', $iMod) : '',
			$bHasAddr ? pack($bShort ? 'c' : 'l', $iAddr) : ''
		);
	}

	/**
	 * @param mixed $mValue
	 * @return string
	 */
	protected function genSlotByteCode($mValue)
	{
		/**
		 * 1 byte type definition
		 * n bytes of data
		 *
		 * type
		 *   1 = nil
		 *   2 = short int / 1 extra byte
		 *   3 = normal int / 4 extra bytes
		 *   4 = double / 8 extra bytes
		 *   5 = short string / 1 + n extra bytes
		 *   6 = normal string / 4 + n extra bytes
		 */

		if(is_int($mValue))
		{
			if($this->isShort($mValue))
			{
				return pack('Cc', 2, $mValue);
			}

			return pack('Cl', 3, $mValue);
		}
		elseif(is_float($mValue))
		{
			return pack('Cd', 4, $mValue);
		}
		elseif(is_string($mValue))
		{
			$iLen = strlen($mValue);

			if($this->isShort($iLen))
			{
				return sprintf("%s%s", pack('Cc', 5, $iLen), $mValue);
			}

			return sprintf("%s%s", pack('Cl', 6, $iLen), $mValue);
		}

		return pack('C', 1);
	}

	/**
	 * @param int $iVal
	 * @return bool
	 */
	protected function isShort($iVal)
	{
		return $iVal <= 127 && $iVal >= -127;
	}

	/**
	 * @param int $iVal
	 * @return bool
	 */
	protected function isUltraShort($iVal)
	{
		return $iVal <= 7 && $iVal >= -7;
	}
}

class etaCompilerVmMemoryAssembler extends etaCompilerAssembler
{
	/**
	 * @var etaVmMemory
	 */
	protected $oMemory;

	/**
	 * @param etaVmMemory $oMemory
	 * @return void
	 */
	public function __construct(etaVmMemory $oMemory)
	{
		$this->oMemory = $oMemory;
	}

	/**
	 * @see etaCompilerAssembler::assemble()
	 */
	public function assemble(etaCompilerContext $oContext)
	{
		$iInstCount = $oContext->getInstCount();

		foreach($oContext->getAllInst() as $oInst)
		{
			$this->addInst($oInst, $iInstCount);
		}

		foreach($oContext->getSlots() as $mSlotValue)
		{
			$this->oMemory->push(etaVmValue::create($mSlotValue));
		}
	}

	/**
	 * @param etaCompilerInst $oInst
	 * @param int $iSlotOffset
	 * @return void
	 */
	protected function addInst(etaCompilerInst $oInst, $iSlotOffset)
	{
		list($bHasAddr, $bIndirect, $bRelative, $iAddr) = $this->getAddrData(
			$oInst->getAddr(), $iSlotOffset
		);

		$this->oMemory->push(new etaVmValueInst(
			$oInst->getOpcode(), $iAddr, $bIndirect, $bRelative
		));
	}
}

interface etaCompilerSrcCodeReader
{
	/**
	 * @return string
	 */
	public function readSrcCode();
}

class etaCompilerSrcCodeReaderString implements etaCompilerSrcCodeReader
{
	/**
	 * @var string
	 */
	protected $sSrcCode;

	/**
	 * @param string $sSrcCode
	 * @return void
	 */
	public function __construct($sSrcCode)
	{
		$this->sSrcCode = $sSrcCode;
	}

	/**
	 * @see etaCompilerSrcCodeReader::readSrcCode()
	 */
	public function readSrcCode()
	{
		return $this->sSrcCode;
	}
}

interface etaCompilerLang
{
	/**
	 * @return etaCompilerContext
	 */
	public function getCompilerContext();

	/**
	 * @return etaBnfDefStruct
	 */
	public function getBnf();

	/**
	 * @param etaCompilerContext $oContext
	 * @return void
	*/
	public function processContext(etaCompilerContext $oContext);
}

abstract class etaCompilerLangDefault implements etaCompilerLang
{
	/**
	 * @see etaCompilerLang::getCompilerContext()
	 */
	public function getCompilerContext()
	{
		return new etaCompilerContext;
	}
}

class etaCompiler
{
	/**
	 * @var etaCompilerLang
	 */
	protected $oLang;

	/**
	 * @var etaCompilerSrcCodeReader
	 */
	protected $oSrcCodeReader;

	/**
	 * @var bool
	 */
	protected $bCacheParser;

	/**
	 * @param etaCompilerLang $oLang
	 * @param etaCompilerSrcCodeReader $oCodeReader
	 * @param bool $bCacheParser
	 * @return void
	 */
	public function __construct(
		etaCompilerLang $oLang,
		etaCompilerSrcCodeReader $oSrcCodeReader,
		$bCacheParser = false
	)
	{
		$this->oLang = $oLang;

		$this->oSrcCodeReader = $oSrcCodeReader;

		$this->bCacheParser = $bCacheParser;
	}

	/**
	 * @return etaCompilerContext
	 */
	public function compile()
	{
		$oContext = $this->oLang->getCompilerContext();

		if(!($oContext instanceof etaCompilerContext))
		{
			throw new Exception('invalid compiler context');
		}

		$oContext->setSyntaxTree($this->getSyntaxTree());

		$this->oLang->processContext($oContext);

		return $oContext;
	}

	/**
	 * @return etaBnfTree
	 */
	protected function getSyntaxTree()
	{
		$oParser = new etaBnfParser(
			$this->oLang->getBnf(),
			$this->oSrcCodeReader->readSrcCode(),
			$this->bCacheParser
		);

		return $oParser->parse();
	}
}
