<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 0x6d72
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

interface etaVmByteCodeReader
{
	/**
	 * @return string
	 */
	public function readByteCode();
}

class etaVmByteCodeReaderString implements etaVmByteCodeReader
{
	/**
	 * @var string
	 */
	protected $sByteCode;

	/**
	 * @param string $sByteCode
	 * @return void
	 */
	public function __construct($sByteCode)
	{
		$this->sByteCode = $sByteCode;
	}

	/**
	 * @see etaVmByteCodeReader::readByteCode()
	 */
	public function readByteCode()
	{
		return $this->sByteCode;
	}
}

abstract class etaVmValue
{
	/**
	 * @return mixed
	 */
	abstract public function getValue();

	/**
	 * @return int
	*/
	abstract public function getSize();

	/**
	 * @param mixed $mValue
	 * @return etaVmValue
	 */
	static public function create($mValue)
	{
		if(is_bool($mValue))
		{
			return new etaVmValueInt($mValue ? 1 : 0);
		}
		elseif(is_int($mValue))
		{
			return new etaVmValueInt($mValue);
		}
		elseif(is_float($mValue))
		{
			return new etaVmValueFloat($mValue);
		}
		elseif(is_string($mValue))
		{
			return new etaVmValueString($mValue);
		}
		elseif(is_array($mValue))
		{
			$oTable = new etaVmValueTable;

			foreach($mValue as $iIndex => $mSubValue)
			{
				$oTable->set(self::create($iIndex), self::create($mSubValue));
			}

			return $oTable;
		}

		return etaVmValueNil::getInstance();
	}

	/**
	 * @param etaVmValue $oVal
	 * @return mixed
	 */
	static public function toNative(etaVmValue $oValue)
	{
		if($oValue instanceof etaVmValueTable)
		{
			$aTable = array();

			foreach($oValue->getValue() as $iIndex => $oSubValue)
			{
				$aTable[$iIndex] = self::toNative($oSubValue);
			}

			return $aTable;
		}

		return $oValue->getValue();
	}

	/**
	 * @param etaVmValue $oVal1
	 * @param etaVmValue $oVal2
	 * @return bool
	 */
	static public function isEqual(etaVmValue $oVal1, etaVmValue $oVal2)
	{
		$sClass = get_class($oVal1);

		if($oVal2 instanceof $sClass)
		{
			return $oVal1->getValue() === $oVal2->getValue();
		}
		elseif(!($oVal1 instanceof etaVmValueNil)
			&& !($oVal2 instanceof etaVmValueNil))
		{
			$aMethod = array($sClass, 'castTo');

			if(is_callable($aMethod))
			{
				$oTmp = call_user_func($aMethod, $oVal2);

				return $oVal1->getValue() === $oTmp->getValue();
			}
		}

		return false;
	}
}

class etaVmValueNil extends etaVmValue
{
	/**
	 * @return etaVmValueNil
	 */
	static public function getInstance()
	{
		static $oInstance;

		if($oInstance === null)
		{
			$oInstance = new self;
		}

		return $oInstance;
	}

	/**
	 * @see etaVmValue::getValue()
	 */
	public function getValue()
	{
		return null;
	}

	/**
	 * @see etaVmValue::getSize()
	 */
	public function getSize()
	{
		return 0;
	}
}

class etaVmValueInt extends etaVmValue
{
	/**
	 * @var int
	 */
	protected $iInt;

	/**
	 * @param int $iInt
	 * @return void
	 */
	public function __construct($iInt = 0)
	{
		$this->iInt = (int) $iInt;
	}

	/**
	 * @see etaVmValue::getValue()
	 */
	public function getValue()
	{
		return $this->iInt;
	}

	/**
	 * @see etaVmValue::getSize()
	 */
	public function getSize()
	{
		return 0;
	}

	/**
	 * @param etaVmValue $oValue
	 * @return etaVmValueInt
	 */
	static public function castTo(etaVmValue $oValue)
	{
		if($oValue instanceof etaVmValueString
			|| $oValue instanceof etaVmValueFloat)
		{
			return new self((int) $oValue->getValue());
		}
		elseif($oValue instanceof self)
		{
			return $oValue;
		}

		return new self;
	}
}

class etaVmValueFloat extends etaVmValue
{
	/**
	 * @var float
	 */
	protected $fFloat;

	/**
	 * @param float $fFloat
	 * @return void
	 */
	public function __construct($fFloat = 0.0)
	{
		$this->fFloat = (float) $fFloat;
	}

	/**
	 * @see etaVmValue::getValue()
	 */
	public function getValue()
	{
		return $this->fFloat;
	}

	/**
	 * @see etaVmValue::getSize()
	 */
	public function getSize()
	{
		return 0;
	}

	/**
	 * @param etaVmValue $oValue
	 * @return etaVmValueFloat
	 */
	static public function castTo(etaVmValue $oValue)
	{
		if($oValue instanceof etaVmValueString
				|| $oValue instanceof etaVmValueInt)
		{
			return new self((float) $oValue->getValue());
		}
		elseif($oValue instanceof self)
		{
			return $oValue;
		}

		return new self;
	}
}

class etaVmValueInst extends etaVmValueInt
{
	/**
	 * @var int
	 */
	protected $iAddr;

	/**
	 * @var bool
	 */
	protected $bIndirect;

	/**
	 * @var bool
	 */
	protected $bRelative;

	/**
	 * @param int $iOpcode
	 * @param int $iAddr
	 * @param bool $bIndirect
	 * @param bool $bRelative
	 * @return void
	 */
	public function __construct($iOpcode, $iAddr, $bIndirect, $bRelative = false)
	{
		parent::__construct($iOpcode);

		$this->iAddr = $iAddr;

		$this->bIndirect = $bIndirect;

		$this->bRelative = $bRelative;
	}

	/**
	 * @return int
	 */
	public function getAddr()
	{
		return $this->iAddr;
	}

	/**
	 * @return bool
	 */
	public function hasIndirectAddr()
	{
		return $this->bIndirect;
	}

	/**
	 * @return bool
	 */
	public function hasRelativeAddr()
	{
		return $this->bRelative;
	}
}

class etaVmValueString extends etaVmValue
{
	/**
	 * @var string
	 */
	protected $sString;

	/**
	 * @param string $sString
	 * @return void
	 */
	public function __construct($sString = "")
	{
		$this->sString = $sString;
	}

	/**
	 * @see etaVmValue::getValue()
	 */
	public function getValue()
	{
		return $this->sString;
	}

	/**
	 * @see etaVmValue::getSize()
	 */
	public function getSize()
	{
		return strlen($this->sString);
	}

	/**
	 * @param etaVmValue $oValue
	 * @return etaVmValueString
	 */
	static public function castTo(etaVmValue $oValue)
	{
		if($oValue instanceof etaVmValueInt
			|| $oValue instanceof etaVmValueFloat)
		{
			return new self((string) $oValue->getValue());
		}
		elseif($oValue instanceof self)
		{
			return $oValue;
		}

		return new self;
	}
}

class etaVmValueTable extends etaVmValue
{
	/**
	 * @var array
	 */
	protected $aTable = array();

	/**
	 * @param etaVmValue $oValue
	 * @return void
	 */
	public function add(etaVmValue $oValue)
	{
		$this->aTable[] = $oValue;
	}

	/**
	 * @param etaVmValue $oIndex
	 * @return mixed
	 */
	protected function validateIndex(etaVmValue $oIndex)
	{
		if($oIndex instanceof etaVmValueInt
			|| $oIndex instanceof etaVmValueString)
		{
			return $oIndex->getValue();
		}

		return null;
	}

	/**
	 * @param etaVmValue $oIndex
	 * @return etaVmValue
	 */
	public function get(etaVmValue $oIndex)
	{
		$mIndex = $this->validateIndex($oIndex);

		if($mIndex !== null && isset($this->aTable[$mIndex]))
		{
			return $this->aTable[$mIndex];
		}

		return etaVmValueNil::getInstance();
	}

	/**
	 * @param etaVmValue $oIndex
	 * @param etaVmValue $oValue
	 * @return void
	 */
	public function set(etaVmValue $oIndex, etaVmValue $oValue)
	{
		$mIndex = $this->validateIndex($oIndex);

		if($mIndex !== null)
		{
			if($oValue instanceof etaVmValueNil)
			{
				if(isset($this->aTable[$mIndex]))
				{
					unset($this->aTable[$mIndex]);
				}

				return;
			}

			$this->aTable[$mIndex] = $oValue;
		}
	}

	/**
	 * @param etaVmValue $oIndex
	 * @return void
	 */
	public function del(etaVmValue $oIndex)
	{
		$mIndex = $this->validateIndex($oIndex);

		if($mIndex !== null && isset($this->aTable[$mIndex]))
		{
			unset($this->aTable[$mIndex]);
		}
	}

	/**
	 * @param etaVmValue $oIndex
	 * @param etaVmValue $oValue
	 * @return void
	 */
	public function next(&$oIndex, &$oValue)
	{
		$mIndex = key($this->aTable);
		$oValue = current($this->aTable);

		next($this->aTable);

		if($mIndex !== null)
		{
			$oIndex = etaVmValue::create($mIndex);
		}
		else
		{
			$oIndex = $oValue = etaVmValueNil::getInstance();
		}
	}

	/**
	 * @return void
	 */
	public function reset()
	{
		reset($this->aTable);
	}

	/**
	 * @see etaVmValue::getValue()
	 */
	public function getValue()
	{
		return $this->aTable;
	}

	/**
	 * @see etaVmValue::getSize()
	 */
	public function getSize()
	{
		return count($this->aTable);
	}

	/**
	 * @param etaVmValue $oValue
	 * @return etaVmValueTable
	 */
	static public function castTo(etaVmValue $oValue)
	{
		if($oValue instanceof self)
		{
			return $oValue;
		}

		return new self;
	}
}

class etaVmMemory
{
	/**
	 * @var array
	 */
	protected $aMemory = array();

	/**
	 * @param int $iAddr
	 * @return int
	 */
	public function getAbsoluteAddr($iAddr)
	{
		if($iAddr < 0)
		{
			return count($this->aMemory) + $iAddr;
		}

		return $iAddr;
	}

	/**
	 * @return int
	 */
	public function size()
	{
		return count($this->aMemory);
	}

	/**
	 * @param int $iAddr
	 * @return etaVmValue
	 */
	public function get($iAddr)
	{
		$iAddr = $this->getAbsoluteAddr($iAddr);

		if(array_key_exists($iAddr, $this->aMemory))
		{
			return $this->aMemory[$iAddr];
		}

		return etaVmValueNil::getInstance();
	}

	/**
	 * @param int $iAddr
	 * @param etaVmValue $oValue
	 * @return void
	 */
	public function set($iAddr, etaVmValue $oValue)
	{
		$iAddr = $this->getAbsoluteAddr($iAddr);

		if(array_key_exists($iAddr, $this->aMemory))
		{
			$this->aMemory[$iAddr] = $oValue;
		}
	}

	/**
	 * @param int $iSize
	 * @return int
	 */
	public function grow($iSize)
	{
		$oValue = etaVmValueNil::getInstance();

		for(;$iSize>0;--$iSize)
		{
			$this->aMemory[] = $oValue;
		}
	}

	/**
	 * @param int $iSize
	 * @return int
	 */
	public function resize($iSize)
	{
		$this->aMemory = array_slice($this->aMemory, 0, $iSize);
	}

	/**
	 * @param etaVmValue $oValue
	 * @return void
	 */
	public function push(etaVmValue $oValue)
	{
		$this->aMemory[] = $oValue;
	}

	/**
	 * @param int $iCount
	 * @return void
	 */
	public function pop($iCount)
	{
		if($iCount > 0)
		{
			$this->aMemory = array_slice($this->aMemory, 0, -1 * $iCount);
		}
	}

	/**
	 * @return void
	 */
	public function clear()
	{
		$this->aMemory = array();
	}
}

class etaVmBytecodeDisassembler
{
	/**
	 * @param etaVmByteCodeReader $oByteCodeReader
	 * @return etaVmMemory
	 */
	public function disassemble(etaVmByteCodeReader $oByteCodeReader)
	{
		$oMemory = new etaVmMemory;

		$sByteCode = $oByteCodeReader->readByteCode();

		if($this->getUnsignedByte($sByteCode) != 0xCC)
		{
			throw new Exception("bytecode doesn't seem to be valid");
		}

		$iCount = $this->getUnsignedShort($sByteCode);

		for(;$iCount>0;--$iCount)
		{
			$oMemory->push($this->readInst($sByteCode));
		}

		while(strlen($sByteCode) > 0)
		{
			$oMemory->push($this->readSlot($sByteCode));
		}

		return $oMemory;
	}

	/**
	 * @param string $sByteCode
	 * @return etaVmValueInst
	 */
	protected function readInst(&$sByteCode)
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

		$iAddr = 0;

		$iOpcode = $this->getUnsignedByte($sByteCode);

		$bHasMod = ($iOpcode & 0x01) == 0x01;

		$iOpcode >>= 1;

		$iMod = $bHasMod ? $this->getUnsignedByte($sByteCode) : 0;

		$bIndirect = ($iMod & 0x10) == 0x10;
		$bRelative = ($iMod & 0x20) == 0x20;
		$bHasAddr = ($iMod & 0x40) == 0x40;
		$bShort = ($iMod & 0x80) == 0x80;

		if($bHasAddr)
		{
			if($bShort)
			{
				$iAddr = $this->getSignedByte($sByteCode);
			}
			else
			{
				$iAddr = $this->getSignedInt($sByteCode);
			}
		}
		elseif($bShort)
		{
			$iAddr = $iMod & 0x07;

			if(($iMod & 0x08) == 0x08)
			{
				$iAddr *= -1;
			}
		}

		return new etaVmValueInst($iOpcode, $iAddr, $bIndirect, $bRelative);
	}

	/**
	 * @param string $sByteCode
	 * @return mixed
	 */
	protected function readSlot(&$sByteCode)
	{
		$iType = $this->getUnsignedByte($sByteCode);

		switch($iType)
		{
			// nil
			case 1:
				return etaVmValueNil::getInstance();

			// short signed byte
			case 2:
				return new etaVmValueInt($this->getSignedByte($sByteCode));

			// signed int
			case 3:
				return new etaVmValueInt($this->getSignedInt($sByteCode));

			// double
			case 4:
				return new etaVmValueFloat($this->getDouble($sByteCode));

			// short string
			case 5:

				$iLen = $this->getSignedByte($sByteCode);

				$sStr = substr($sByteCode, 0, $iLen);

				$sByteCode = substr($sByteCode, $iLen);

				return new etaVmValueString($sStr);

			// long string
			case 6:

				$iLen = $this->getSignedInt($sByteCode);

				$sStr = substr($sByteCode, 0, $iLen);

				$sByteCode = substr($sByteCode, $iLen);

				return new etaVmValueString($sStr);
		}

		throw new Exception(sprintf(
			'cannot read slot from byte code; invalid type %d', $iType
		));
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function getUnsignedByte(&$sStr)
	{
		$aResult = unpack('C', $sStr);

		$sStr = substr($sStr, 1);

		return $aResult[1];
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function getUnsignedShort(&$sStr)
	{
		$aResult = unpack('S', $sStr);

		$sStr = substr($sStr, 2);

		return $aResult[1];
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function getSignedByte(&$sStr)
	{
		$aResult = unpack('c', $sStr);

		$sStr = substr($sStr, 1);

		return $aResult[1];
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function getSignedInt(&$sStr)
	{
		$aResult = unpack('l', $sStr);

		$sStr = substr($sStr, 4);

		return $aResult[1];
	}

	/**
	 * @param string $sStr
	 * @return double
	 */
	protected function getDouble(&$sStr)
	{
		$aResult = unpack('d', $sStr);

		$sStr = substr($sStr, 8);

		return $aResult[1];
	}
}

interface etaVmIo
{
	/**
	 * @return mixed
	 */
	public function in();

	/**
	 * @param mixed $mValue
	 * @return void
	 */
	public function out($mValue);

	/**
	 * @param mixed $mControlValue
	 * @return mixed
	 */
	public function control($mControlValue);
}

class etaVmIoArray implements etaVmIo
{
	/**
	 * @var array
	 */
	protected $aInputData;

	/**
	 * @var array
	 */
	protected $aOutputData;

	/**
	 * @param array $aInputData
	 * @return void
	 */
	public function __construct(array $aInputData)
	{
		$this->aInputData = $aInputData;

		$this->reset();
	}

	/**
	 * @see etaVmIo::in()
	 */
	public function in()
	{
		$mKey = key($this->aInputData);

		if($mKey === null)
		{
			return null;
		}

		$mValue = current($this->aInputData);

		next($this->aInputData);

		return $mValue;
	}

	/**
	 * @see etaVmIo::out()
	 */
	public function out($mValue)
	{
		$this->aOutputData[] = $mValue;
	}

	/**
	 * @see etaVmIo::control()
	 */
	public function control($mControlValue)
	{
		switch($mControlValue)
		{
			case 'reset':
				$this->reset();
				break;
		}
	}

	/**
	 * @return void
	 */
	public function reset()
	{
		reset($this->aInputData);

		$this->aOutputData = array();
	}

	/**
	 * @return array
	 */
	public function getOutputData()
	{
		return $this->aOutputData;
	}
}

interface etaVmSysLink
{
	/**
	 * @param string $sFunc
	 * @param array $aParams
	 * @return mixed
	 */
	public function callSysFunc($sFunc, array $aParams);
}

class etaVmSysLinkCallback implements etaVmSysLink
{
	protected $aCallbacks;

	public function __construct(array $aCallbacks)
	{
		$this->aCallbacks = $aCallbacks;
	}

	public function callSysFunc($sFunc, array $aParams)
	{
		if(isset($this->aCallbacks[$sFunc]))
		{
			$mCallback = $this->aCallbacks[$sFunc];

			if(is_callable($mCallback))
			{
				return call_user_func_array($mCallback, $aParams);
			}
		}

		return null;
	}
}

class etaVm
{
	/**
	 * @var etaVmMemory
	 */
	protected $oMemory;

	/**
	 * @var etaVmIo
	 */
	protected $oIo;

	/**
	 * @var etaVmSysLink
	 */
	protected $oSysLink;

	/**
	 * @var int
	 */
	protected $iInstPointer;

	/**
	 * @var int
	 */
	protected $iFramePointer;

	/**
	 * @var bool
	 */
	protected $bActive;

	/**
	 * @param etaVmByteCodeReader $oByteCodeReader
	 * @param etaVmIo $oIo
	 * @param etaVmSysLink $oSysLink
	 * @return void
	 */
	public function __construct(
		etaVmMemory $oMemory,
		etaVmIo $oIo = null,
		etaVmSysLink $oSysLink = null
	)
	{
		$this->oMemory = $oMemory;

		$this->oIo = $oIo;

		$this->oSysLink = $oSysLink;

		$this->restart();
	}

	/**
	 * @return etaVmMemory
	 */
	public function getMemory()
	{
		return $this->oMemory;
	}

	/**
	 * @return etaVmIo
	 */
	public function getIo()
	{
		return $this->oIo;
	}

	/**
	 * @param etaVmIo $oIo
	 * @return void
	 */
	public function setIo(etaVmIo $oIo)
	{
		$this->oIo = $oIo;
	}

	/**
	 * @return etaVmSysLink
	 */
	public function getSysLink()
	{
		return $this->oSysLink;
	}

	/**
	 * @param etaVmSysLink $oSysLink
	 * @return void
	 */
	public function setSysLink(etaVmSysLink $oSysLink)
	{
		$this->oSysLink = $oSysLink;
	}

	/**
	 * @return void
	 */
	public function restart()
	{
		$this->iInstPointer = 0;

		$this->iFramePointer = 0;

		$this->bActive = true;
	}

	/**
	 * @param int $iMax
	 * @return int
	 */
	public function exec($iMax = 0)
	{
		$i = 0;

		while($this->bActive)
		{
			$oInst = $this->oMemory->get($this->iInstPointer);

			if(!($oInst instanceof etaVmValueInst))
			{
				throw new Exception(sprintf(
					'invalid instruction at location %d',
					$this->iInstPointer
				));
			}

			++$this->iInstPointer;

			$this->execInst($oInst);

			++$i;

			if($iMax > 0 && --$iMax == 0)
			{
				break;
			}
		}

		return $i;
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execInst($oInst)
	{
		$this->execGenericInst($oInst);
		$this->execMathInst($oInst);
		$this->execCmpInst($oInst);
		$this->execStackInst($oInst);
		$this->execJumpInst($oInst);
		$this->execIoInst($oInst);
		$this->execTableInst($oInst);
		$this->execStringInst($oInst);
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execGenericInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_HLT:

				$this->bActive = false;

				break;

			case etaOpcode::OP_SYS:

				$aParams = array();

				$iCount = etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue();

				for($i=-1*($iCount+1);$i<-1;++$i)
				{
					$aParams[] = etaVmValue::toNative($this->oMemory->get($i));
				}

				$sFunc = etaVmValueString::castTo(
					$this->oMemory->get(-1)
				)->getValue();

				$this->oMemory->pop($iCount + 1);

				$oResult = null;

				if($this->oSysLink instanceof etaVmSysLink)
				{
					$oResult = etaVmValue::create(
						$this->oSysLink->callSysFunc($sFunc, $aParams)
					);
				}

				if(!$oResult instanceof etaVmValue)
				{
					$oResult = etaVmValueNil::getInstance();
				}

				$this->oMemory->push($oResult);

				break;

			case etaOpcode::OP_SIZE:

				$this->oMemory->push(new etaVmValueInt(
					$this->getAddrValue($oInst)->getSize()
				));

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execMathInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_ADD:

				$oOp1 = etaVmValueFloat::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueFloat::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueFloat(
					$oOp1->getValue() + $oOp2->getValue()
				));

				break;

			case etaOpcode::OP_SUB:

				$oOp1 = etaVmValueFloat::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueFloat::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueFloat(
					$oOp1->getValue() - $oOp2->getValue()
				));

				break;

			case etaOpcode::OP_MUL:

				$oOp1 = etaVmValueFloat::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueFloat::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueFloat(
					$oOp1->getValue() * $oOp2->getValue()
				));

				break;

			case etaOpcode::OP_DIV:

				$oOp1 = etaVmValueFloat::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueFloat::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueFloat(
					$oOp1->getValue() / $oOp2->getValue()
				));

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execCmpInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_EQ:

				$oOp1 = $this->oMemory->get(-2);
				$oOp2 = $this->oMemory->get(-1);

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueInt(
					etaVmValue::isEqual($oOp1, $oOp2)
				));

				break;

			case etaOpcode::OP_LESS:

				$oOp1 = etaVmValueFloat::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueFloat::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueInt(
					$oOp1->getValue() < $oOp2->getValue()
				));

				break;

			case etaOpcode::OP_AND:

				$oOp1 = etaVmValueInt::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueInt::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueInt(
					((bool) $oOp1->getValue()) && ((bool) $oOp2->getValue())
				));

				break;

			case etaOpcode::OP_OR:

				$oOp1 = etaVmValueInt::castTo($this->oMemory->get(-2));
				$oOp2 = etaVmValueInt::castTo($this->oMemory->get(-1));

				$this->oMemory->pop(2);

				$this->oMemory->push(new etaVmValueInt(
					((bool) $oOp1->getValue()) || ((bool) $oOp2->getValue())
				));

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execStackInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_PUSH:

				$this->oMemory->push($this->getAddrValue($oInst));

				break;

			case etaOpcode::OP_PUSHNIL:

				$this->oMemory->push(etaVmValueNil::getInstance());

				break;

			case etaOpcode::OP_POP:

				$this->oMemory->pop(etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue());

				break;

			case etaOpcode::OP_RPL:

				$this->oMemory->set(
					etaVmValueInt::castTo(
						$this->getAddrValue($oInst)
					)->getValue(),
					$this->oMemory->get(-1)
				);

				$this->oMemory->pop(1);

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execJumpInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_JMP:

				$this->iInstPointer = etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue();

				break;

			case etaOpcode::OP_JT:

				$mVal = (bool) etaVmValueInt::castTo(
					$this->oMemory->get(-1)
				)->getValue();

				$this->oMemory->pop(1);

				if($mVal)
				{
					$this->iInstPointer = etaVmValueInt::castTo(
						$this->getAddrValue($oInst)
					)->getValue();
				}

				break;

			case etaOpcode::OP_JF:

				$mVal = (bool) etaVmValueInt::castTo(
					$this->oMemory->get(-1)
				)->getValue();

				$this->oMemory->pop(1);

				if(!$mVal)
				{
					$this->iInstPointer = etaVmValueInt::castTo(
						$this->getAddrValue($oInst)
					)->getValue();
				}

				break;

			case etaOpcode::OP_CALL;

				$iParamCount = etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue();

				$iFuncAddr = etaVmValueInt::castTo(
					$this->oMemory->get(-1)
				)->getValue();

				$aParams = array();

				for($i=-1*($iParamCount+1);$i<-1;++$i)
				{
					$aParams[] = $this->oMemory->get($i);
				}

				$this->oMemory->pop($iParamCount + 1);

				$this->oMemory->push(new etaVmValueInt($this->iInstPointer));
				$this->oMemory->push(new etaVmValueInt($this->iFramePointer));

				$this->iInstPointer = $iFuncAddr;
				$this->iFramePointer = $this->oMemory->size();

				foreach($aParams as $oParam)
				{
					$this->oMemory->push($oParam);
				}

				break;

			case etaOpcode::OP_RET:

				$oReturnValue = $this->oMemory->get(-1);

				$this->oMemory->resize($this->iFramePointer);

				$this->iInstPointer = etaVmValueInt::castTo(
					$this->oMemory->get(-2)
				)->getValue();

				$this->iFramePointer = etaVmValueInt::castTo(
					$this->oMemory->get(-1)
				)->getValue();

				$this->oMemory->pop(2);

				$this->oMemory->push($oReturnValue);

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execIoInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_IN:

				if($this->oIo instanceof etaVmIo)
				{
					$this->oMemory->push(etaVmValue::create($this->oIo->in()));
				}
				else
				{
					$this->oMemory->push(etaVmValueNil::getInstance());
				}

				break;

			case etaOpcode::OP_OUT:

				if($this->oIo instanceof etaVmIo)
				{
					$oValue = $this->getAddrValue($oInst);

					if(!($oValue instanceof etaVmValueNil))
					{
						$this->oIo->out(etaVmValue::toNative($oValue));
					}
				}

				break;

			case etaOpcode::OP_IOC:

				if($this->oIo instanceof etaVmIo)
				{
					$this->oMemory->push(etaVmValue::create($this->oIo->control(
						etaVmValue::toNative($this->getAddrValue($oInst))
					)));
				}
				else
				{
					$this->oMemory->push(etaVmValueNil::getInstance());
				}

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execTableInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_TAB:

				$oTable = new etaVmValueTable;

				$iCount = etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue();

				for($i=-1*$iCount;$i<0;++$i)
				{
					$oTable->add($this->oMemory->get($i));
				}

				$this->oMemory->pop($iCount);

				$this->oMemory->push($oTable);

				break;

			case etaOpcode::OP_GET:

				$oTable = etaVmValueTable::castTo($this->getAddrValue($oInst));
				$oIndex = $this->oMemory->get(-1);

				$this->oMemory->pop(1);

				$this->oMemory->push($oTable->get($oIndex));

				break;

			case etaOpcode::OP_PUT:

				$oTable = etaVmValueTable::castTo($this->getAddrValue($oInst));
				$oIndex = $this->oMemory->get(-2);
				$oValue = $this->oMemory->get(-1);

				$oTable->set($oIndex, $oValue);

				$this->oMemory->pop(2);

				break;

			case etaOpcode::OP_DEL:

				$oTable = etaVmValueTable::castTo($this->getAddrValue($oInst));
				$oIndex = $this->oMemory->get(-1);

				$this->oMemory->pop(1);

				$oTable->del($oIndex);

				break;

			case etaOpcode::OP_NXT:

				$oTable = etaVmValueTable::castTo($this->getAddrValue($oInst));

				$oTable->next($oIndex, $oValue);

				$this->oMemory->push($oIndex);
				$this->oMemory->push($oValue);

				break;

			case etaOpcode::OP_RES:

				$oTable = etaVmValueTable::castTo($this->getAddrValue($oInst));

				$oTable->reset();

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return void
	 */
	protected function execStringInst(etaVmValueInst $oInst)
	{
		switch($oInst->getValue())
		{
			case etaOpcode::OP_CONCAT:

				$aValues = array();

				$iCount = etaVmValueInt::castTo(
					$this->getAddrValue($oInst)
				)->getValue();

				for($i=-1*$iCount;$i<0;++$i)
				{
					$aValues[] = etaVmValueString::castTo(
						$this->oMemory->get($i)
					)->getValue();
				}

				$this->oMemory->pop($iCount);

				$this->oMemory->push(new etaVmValueString(
					implode('', $aValues)
				));

				break;
		}
	}

	/**
	 * @param etaVmValueInst $oInst
	 * @return etaVmValue
	 */
	protected function getAddrValue(etaVmValueInst $oInst)
	{
		$iAddr = $oInst->getAddr();

		if($iAddr >= 0 && $oInst->hasRelativeAddr())
		{
			$iAddr += $this->iFramePointer;
		}

		if($oInst->hasIndirectAddr())
		{
			return $this->oMemory->get($iAddr);
		}

		return new etaVmValueInt($iAddr);
	}
}

?>