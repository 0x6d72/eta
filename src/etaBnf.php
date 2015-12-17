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

abstract class etaBnfDefComp
{
	/**
	 * @var int
	 */
	const QUANT_1 = 1;

	/**
	 * @var int
	 */
	const QUANT_01 = 2;

	/**
	 * @var int
	 */
	const QUANT_0N = 3;

	/**
	 * @var int
	 */
	const QUANT_1N = 4;

	/**
	 * @var int
	 */
	const MOD_NONE = 0;

	/**
	 * @var int
	 */
	const MOD_NO_TREE = 1;

	/**
	 * @var int
	 */
	protected $iQuant = self::QUANT_1;

	/**
	 * @var int
	 */
	protected $iMod = self::MOD_NONE;

	/**
	 * @param int $iQuant
	 * @reutrn void
	 */
	public function setQuant($iQuant)
	{
		$this->iQuant = $iQuant;
	}

	/**
	 * @return int
	 */
	public function getQuant()
	{
		return $this->iQuant;
	}

	/**
	 * @param int $iMod
	 * @return void
	 */
	public function addMod($iMod)
	{
		$this->iMod |= $iMod;
	}

	/**
	 * @param int $iMod
	 * @return void
	 */
	public function delMod($iMod)
	{
		$this->iMod &= ~$iMod;
	}

	/**
	 * @param int $iMod
	 * @return bool
	 */
	public function hasMod($iMod)
	{
		return $this->iMod & $iMod == $iMod;
	}

	/**
	 * @return int
	 */
	public function getMod()
	{
		return $this->iMod;
	}
}

class etaBnfDefCompRef extends etaBnfDefComp
{
	/**
	 * @var array
	 */
	protected $aRefs = array();

	/**
	 * @param string $sRef
	 * @return void
	 */
	public function addRef($sRef)
	{
		$this->aRefs[] = $sRef;
	}

	/**
	 * @return array
	 */
	public function getRefs()
	{
		return $this->aRefs;
	}
}

class etaBnfDefCompSet extends etaBnfDefComp
{
	/**
	 * @var string
	 */
	protected $sSet;

	/**
	 * @var bool
	 */
	protected $bNegated = false;

	/**
	 * @param string $aSet
	 * @return void
	 */
	public function __construct($sSet)
	{
		$this->sSet = $sSet;
	}

	/**
	 * @return string
	 */
	public function getSet()
	{
		return $this->sSet;
	}

	/**
	 * @return void
	 */
	public function negate()
	{
		$this->bNegated = true;
	}

	/**
	 * @return bool
	 */
	public function isNegated()
	{
		return $this->bNegated;
	}
}

class etaBnfDefCompStr extends etaBnfDefComp
{
	/**
	 * @var string
	 */
	protected $sStr;

	/**
	 * @var bool
	 */
	protected $bNegated = false;

	/**
	 * @param string $sStr
	 */
	public function __construct($sStr)
	{
		$this->sStr = $sStr;
	}

	/**
	 * @return string
	 */
	public function getStr()
	{
		return $this->sStr;
	}

	/**
	 * @return void
	 */
	public function negate()
	{
		$this->bNegated = true;
	}

	/**
	 * @return bool
	 */
	public function isNegated()
	{
		return $this->bNegated;
	}
}

class etaBnfDef
{
	/**
	 * @var string
	 */
	protected $sName;

	/**
	 * @var array
	 */
	protected $aComps = array();

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

	/**
	 * @param etaBnfDefComp $oComp
	 * @return void
	 */
	public function addComp(etaBnfDefComp $oComp)
	{
		$this->aComps[] = $oComp;
	}

	/**
	 * @return array
	 */
	public function getComps()
	{
		return $this->aComps;
	}

	/**
	 * @return etaBnfDefComp
	 */
	public function getLastComp()
	{
		$oComp = end($this->aComps);

		if($oComp instanceof etaBnfDefComp)
		{
			return $oComp;
		}

		throw new Exception('there are no components in this definition');
	}

	/**
	 * @return int
	 */
	public function getCompCount()
	{
		return count($this->aComps);
	}
}

class etaBnfDefStruct
{
	/**
	 * @var string
	 */
	const MAIN_DEF = 'main';

	/**
	 * @var string
	 */
	protected $sHash;

	/**
	 * @var array
	 */
	protected $aDefs = array();

	/**
	 * @param array $aDefs
	 * @return void
	 */
	public function __construct($sHash)
	{
		$this->sHash = $sHash;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->sHash;
	}

	/**
	 * @param etaBnfDef $oDef
	 * @return void
	 */
	public function addDef(etaBnfDef $oDef)
	{
		$this->aDefs[$oDef->getName()] = $oDef;
	}

	/**
	 * @param string $sName
	 * @return bool
	 */
	public function hasDef($sName)
	{
		return isset($this->aDefs[$sName]);
	}

	/**
	 * @param string $sName
	 * @return etaBnfDef
	 */
	public function getDef($sName)
	{
		if($this->hasDef($sName))
		{
			return $this->aDefs[$sName];
		}

		throw new Exception(sprintf(
			'there is no definition named "%s"', $sName
		));
	}

	/**
	 * @return etaBnfDef
	 */
	public function getMainDef()
	{
		return $this->getDef(self::MAIN_DEF);
	}

	/**
	 * @return etaBnfDef
	 */
	public function getLastDef()
	{
		$oDef = end($this->aDefs);

		if($oDef instanceof etaBnfDef)
		{
			return $oDef;
		}

		throw new Exception(
			'there are no definitions in this structure'
		);
	}

	/**
	 * @return array
	 */
	public function getDefs()
	{
		return $this->aDefs;
	}

	/**
	 * @return int
	 */
	public function getDefCount()
	{
		return count($this->aDefs);
	}
}

class etaBnfDefParser extends etaBnfBasicParser
{
	/**
	 * @var string
	 */
	static protected $sSpaceChars = " \r\n\t";

	/**
	 * @var string
	 */
	static protected $sTokenChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';

	/**
	 * @var string
	 */
	static protected $sQuantifierChars = '*+?';

	/**
	 * @var array
	 */
	static protected $aQuantifierMapping = array(
		'*' => etaBnfDefComp::QUANT_0N,
		'+' => etaBnfDefComp::QUANT_1N,
		'?' => etaBnfDefComp::QUANT_01
	);

	/**
	 * @var string
	 */
	static protected $sModifierChars = 'n';

	/**
	 * @var array
	 */
	static protected $aModifierMapping = array(
		'n' => etaBnfDefComp::MOD_NO_TREE
	);

	/**
	 * @var etaBnfDefStruct
	 */
	protected $oStruct;

	/**
	 * @var bool
	 */
	protected $bParsed;

	/**
	 * @param string $sBnf
	 * @return void
	 */
	public function __construct($sBnf)
	{
		$this->reset($sBnf);

		$this->oStruct = new etaBnfDefStruct($this->getBnfHash());

		$this->bParsed = false;
	}

	/**
	 * @return string
	 */
	protected function getBnfHash()
	{
		return base_convert(crc32($this->sCode), 10, 35);
	}

	/**
	 * @return etaBnfDefStruct
	 */
	public function parse()
	{
		if($this->bParsed === false)
		{
			$this->parseBnf();

			if(!$this->reachedEnd())
			{
				throw new Exception(
					$this->buildErrorMsg('cannot parse bnf definition')
				);
			}

			$this->bParsed = true;
		}

		return $this->oStruct;
	}

	/**
	 * @return void
	 */
	protected function parseBnf()
	{
		$this->space();

		while($this->def());
	}

	/**
	 * @return bool
	 */
	protected function def()
	{
		if(($sName = $this->token()) !== false && $this->nextOneIs('='))
		{
			$this->oStruct->addDef(new etaBnfDef($sName));

			$this->space();

			while($this->comp())
			{
				$this->space();
			}

			if($this->oStruct->getLastDef()->getCompCount() > 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function space()
	{
		if(($iLen = $this->matchesManyOf(self::$sSpaceChars)) > 0)
		{
			$this->consumeSilent($iLen);
		}
	}

	/**
	 * @return string|bool
	 */
	protected function token()
	{
		if(($iLen = $this->matchesManyOf(self::$sTokenChars)) > 0)
		{
			$sToken = $this->consume($iLen);

			$this->space();

			return $sToken;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function comp()
	{
		$iSavePoint = $this->save();

		if($this->ref())
		{
			$this->discard($iSavePoint);

			return true;
		}

		$this->restore($iSavePoint);

		$iSavePoint = $this->save();

		if($this->set())
		{
			$this->discard($iSavePoint);

			return true;
		}

		$this->restore($iSavePoint);

		$iSavePoint = $this->save();

		if($this->str())
		{
			$this->discard($iSavePoint);

			return true;
		}

		$this->restore($iSavePoint);

		return false;
	}

	/**
	 * @return bool
	 */
	protected function ref()
	{
		if($this->nextOneIs('<'))
		{
			$this->space();

			$oRef = new etaBnfDefCompRef;

			while(($sToken = $this->token()) !== false)
			{
				$oRef->addRef($sToken);
			}

			if($this->nextOneIs('>'))
			{
				$oRef->setQuant($this->quant());
				$oRef->addMod($this->mod());

				$this->oStruct->getLastDef()->addComp($oRef);

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function set()
	{
		$bNegate = $this->nextOneIs('!');

		if($this->nextOneIs('['))
		{
			if(($iLen = $this->matchesNotManyOf(']')) > 0)
			{
				$oSet = new etaBnfDefCompSet(
					$this->extendSet($this->consume($iLen))
				);

				if($bNegate)
				{
					$oSet->negate();
				}

				if($this->nextOneIs(']'))
				{
					$oSet->setQuant($this->quant());
					$oSet->addMod($this->mod());

					$this->oStruct->getLastDef()->addComp($oSet);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function str()
	{
		$bNegate = $this->nextOneIs('!');

		if($this->nextOneIs('"'))
		{
			$oStr = new etaBnfDefCompStr(
				$this->replaceEscapeSequences($this->consume(
					$this->matchesNotManyOf('"')
				))
			);

			if($bNegate)
			{
				$oStr->negate();
			}

			if($this->nextOneIs('"'))
			{
				$oStr->setQuant($this->quant());
				$oStr->addMod($this->mod());

				$this->oStruct->getLastDef()->addComp($oStr);

				return true;
			}

		}

		return false;
	}

	/**
	 * @return int
	 */
	protected function quant()
	{
		if(($iLen = $this->matchesOneOf(self::$sQuantifierChars)) > 0)
		{
			return self::$aQuantifierMapping[$this->consume($iLen)];
		}

		return etaBnfDefComp::QUANT_1;
	}

	/**
	 * @return int
	 */
	protected function mod()
	{
		if(($iLen = $this->matchesManyOf(self::$sModifierChars)) > 0)
		{
			$iMod = etaBnfDefComp::MOD_NONE;

			foreach(str_split($this->consume($iLen)) as $sMod)
			{
				$iMod |= self::$aModifierMapping[$sMod];
			}

			return $iMod;
		}

		return etaBnfDefComp::MOD_NONE;
	}

	/**
	 * @param string $sSet
	 * @return string
	 */
	protected function extendSet($sSet)
	{
		$sSet = $this->replaceEscapeSequences($sSet);

		$sPattern = '@(.)\\-(.)@s';

		if(preg_match_all(
			$sPattern, $sSet, $aMatches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE
		) > 0)
		{
			foreach(array_reverse($aMatches) as $aMatch)
			{
				$sSet = substr_replace(
					$sSet,
					implode('',	array_map(
						'chr', range(ord($aMatch[1][0]), ord($aMatch[2][0]))
					)),
					$aMatch[0][1],
					strlen($aMatch[0][0])
				);
			}
		}

		return $sSet;
	}

	/**
	 * @param string $sStr
	 * @return string
	 */
	protected function replaceEscapeSequences($sStr)
	{
		$sStr = str_replace(
			array('\r', '\n', '\t'),
			array("\r", "\n", "\t"),
			$sStr
		);

		while(preg_match('@\\\\x([\\da-fA-F]{2})@', $sStr, $aMatches, PREG_OFFSET_CAPTURE) > 0)
		{
			$sStr = substr_replace($sStr, chr(hexdec($aMatches[1][0])), $aMatches[0][1], strlen($aMatches[0][0]));
		}

		return $sStr;
	}
}

abstract class etaBnfTreeNode
{
	/**
	 * @var etaBnfTreeNodeBranch
	 */
	protected $oParentNode = null;

	/**
	 * @var mixed
	 */
	protected $mProcessedValue = null;

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return void
	 */
	public function setParentNode(etaBnfTreeNodeBranch $oNode)
	{
		$this->oParentNode = $oNode;
	}

	/**
	 * @return etaBnfTreeNodeBranch
	 */
	public function getParentNode()
	{
		return $this->oParentNode;
	}

	/**
	 * @param mixed $mValue
	 * @return void
	 */
	public function setProcessedValue($mValue)
	{
		$this->mProcessedValue = $mValue;
	}

	/**
	 * @return mixed
	 */
	public function getProcessedValue()
	{
		return $this->mProcessedValue;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if($this->mProcessedValue !== null)
		{
			return $this->mProcessedValue;
		}

		return $this->getRawValue();
	}

	/**
	 * @return string
	 */
	abstract public function getRawValue();
}

class etaBnfTreeNodeBranch extends etaBnfTreeNode
{
	/**
	 * @var string
	 */
	protected $sName;

	/**
	 * @var array
	 */
	protected $aSubNodes = array();

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

	/**
	 * @param etaBnfTreeNode $oNode
	 * @return void
	 */
	public function addSubNode(etaBnfTreeNode $oNode)
	{
		$oNode->setParentNode($this);

		$this->aSubNodes[] = $oNode;
	}

	/**
	 * @param int $iNode
	 * @return bool
	 */
	public function hasSubNode($iNode)
	{
		return isset($this->aSubNodes[$iNode]);
	}

	/**
	 * @param int $iNode
	 * @return etaBnfTreeNode
	 */
	public function getSubNode($iNode)
	{
		if($this->hasSubNode($iNode))
		{
			return $this->aSubNodes[$iNode];
		}

		throw new Exception(sprintf('subnode %d does not exist', $iNode));
	}

	/**
	 * @return array
	 */
	public function getSubNodes()
	{
		return $this->aSubNodes;
	}

	/**
	 * @return int
	 */
	public function getSubNodeCount()
	{
		return count($this->aSubNodes);
	}

	/**
	 * @see etaBnfTreeNode::getRawValue()
	 */
	public function getRawValue()
	{
		$aValues = array();

		foreach($this->aSubNodes as $oSubNode)
		{
			$aValues[] = $oSubNode->getRawValue();
		}

		return implode('', $aValues);
	}
}

class etaBnfTreeNodeLeaf extends etaBnfTreeNode
{
	/**
	 * @var string
	 */
	protected $sValue;

	/**
	 * @param string $sValue
	 * @return void
	 */
	public function __construct($sValue)
	{
		$this->sValue = $sValue;
	}

	/**
	 * @see etaBnfTreeNode::getRawValue()
	 */
	public function getRawValue()
	{
		return $this->sValue;
	}
}

interface etaBnfTreeTraverseCallback
{
	/**
	 * @param etaBnfTreeNode $oNode
	 * @param int $iDepth
	 * @return void
	 */
	public function handleNodePre(etaBnfTreeNode $oNode, $iDepth);

	/**
	 * @param etaBnfTreeNode $oNode
	 * @param int $iDepth
	 * @return void
	 */
	public function handleNodePost(etaBnfTreeNode $oNode, $iDepth);
}

class etaBnfTreeTraverseCallbackNone implements etaBnfTreeTraverseCallback
{
	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePre()
	 */
	public function handleNodePre(etaBnfTreeNode $oNode, $iDepth)
	{
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePost()
	 */
	public function handleNodePost(etaBnfTreeNode $oNode, $iDepth)
	{
	}
}

class etaBnfTree
{
	/**
	 * @var etaBnfTreeNode
	 */
	protected $oNode;

	/**
	 * @param etaBnfTreeNode $oNode
	 * @return void
	 */
	public function __construct(etaBnfTreeNode $oNode)
	{
		$this->oNode = $oNode;
	}

	/**
	 * @return etaBnfTreeNode
	 */
	public function getNode()
	{
		return $this->oNode;
	}

	/**
	 * @param etaBnfTreeTraverseCallback $oCallback
	 * @return void
	 */
	public function traverse(etaBnfTreeTraverseCallback $oCallback)
	{
		if($oCallback instanceof etaBnfTreeTraverseCallbackNone)
		{
			return;
		}

		$this->traverseNode($this->oNode, $oCallback, 0);
	}

	/**
	 * @param etaBnfTreeNode $oNode
	 * @param etaBnfTreeTraverseCallback $oCallback
	 * @param int $iDepth
	 * @return void
	 */
	protected function traverseNode(
		etaBnfTreeNode $oNode,
		etaBnfTreeTraverseCallback $oCallback,
		$iDepth
	)
	{
		if(($bResult = $oCallback->handleNodePre($oNode, $iDepth)) === null)
		{
			$bResult = true;
		}

		if($bResult && $oNode instanceof etaBnfTreeNodeBranch)
		{
			$iSubDepth = $iDepth + 1;

			foreach($oNode->getSubNodes() as $oSubNode)
			{
				$this->traverseNode($oSubNode, $oCallback, $iSubDepth);
			}
		}

		$oCallback->handleNodePost($oNode, $iDepth);
	}
}

class etaBnfParser
{
	/**
	 * @var string
	 */
	protected $sBnf;

	/**
	 * @var string
	 */
	protected $sCode;

	/**
	 * @var bool
	 */
	protected $bUseFile;

	/**
	 * @param string $sBnf
	 * @param string $sCode
	 * @param bool $bUseFile
	 * @return void
	 */
	public function __construct($sBnf, $sCode, $bUseFile = false)
	{
		$this->sBnf = $sBnf;

		$this->sCode = $sCode;

		$this->bUseFile = $bUseFile;
	}

	/**
	 * @return etaBnfTree
	 */
	public function parse()
	{
		return $this->getParser()->parse($this->sCode);
	}

	/**
	 * @return etaBnfGeneratedParser
	 */
	protected function getParser()
	{
		$sParserClassName = $this->getParserClassName();
		$sParserClassFile = $this->getParserClassFile($sParserClassName);

		if(!$this->loadParserFromFile($sParserClassFile))
		{
			$this->generateParser($sParserClassName, $sParserClassFile);
		}

		$oParser = new $sParserClassName;

		if($oParser instanceof etaBnfGeneratedParser)
		{
			return $oParser;
		}

		throw new Exception('invalid parser');
	}

	/**
	 * @param string $sFile
	 * @return bool
	 */
	protected function loadParserFromFile($sFile)
	{
		if($this->bUseFile && is_readable($sFile))
		{
			require $sFile;

			return true;
		}

		return false;
	}

	/**
	 * @param string $sFile
	 * @param string $sContent
	 * @return void
	 */
	protected function writeParserToFile($sFile, $sContent)
	{
		if($this->bUseFile && is_writeable(dirname($sFile)))
		{
			file_put_contents($sFile, '<?php ' . $sContent . ' ?>');
		}
	}

	/**
	 * @param string $sParserClassName
	 * @param string $sParserClassFile
	 * @return void
	 */
	protected function generateParser($sParserClassName, $sParserClassFile)
	{
		$oBnfParser = new etaBnfDefParser($this->sBnf);

		$oGenerator = new etaBnfParserGenerator(
			$sParserClassName, $oBnfParser->parse()
		);

		$sParserContent = $oGenerator->generateParser();

		$this->writeParserToFile($sParserClassFile, $sParserContent);

		eval($sParserContent);
	}

	/**
	 * @return string
	 */
	protected function getBnfHash()
	{
		return base_convert(crc32($this->sBnf), 10, 35);
	}

	/**
	 * @return string
	 */
	protected function getParserClassName()
	{
		return 'etaBnfParser_' . $this->getBnfHash();
	}

	/**
	 * @param string $sParserClassName
	 * @return string
	 */
	protected function getParserClassFile($sParserClassName)
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR .
			$sParserClassName . '.php';
	}
}

abstract class etaBnfBasicParser
{
	/**
	 * @var int
	 */
	protected $iOffset;

	/**
	 * @var int
	 */
	protected $iFurthestOffset;

	/**
	 * @var string
	 */
	protected $sCode;

	/**
	 * @var int
	 */
	protected $iCodeLen;

	/**
	 * @var array
	 */
	protected $aSavePoints;

	/**
	 * @var int
	 */
	protected $iSavePointCount;

	/**
	 * @param string $sCode
	 * @return void
	 */
	protected function reset(&$sCode)
	{
		$this->iOffset = 0;
		$this->iFurthestOffset = 0;
		$this->sCode = $sCode;
		$this->iCodeLen = strlen($this->sCode);
		$this->aSavePoints = array();
		$this->iSavePointCount = 0;
	}

	/**
	 * @param string $sStr
	 * @return bool
	 */
	protected function nextOneIs($sStr)
	{
		if($this->iOffset < $this->iCodeLen
			&& $this->sCode{$this->iOffset} === $sStr)
		{
			$this->consumeSilent(1);

			return true;
		}

		return false;
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function matches($sStr)
	{
		if($this->iOffset < $this->iCodeLen)
		{
			$iLen = strlen($sStr);

			if(substr_compare($this->sCode, $sStr, $this->iOffset, $iLen) === 0)
			{
				return $iLen;
			}
		}

		return 0;
	}

	/**
	 * @param string $sStr
	 * @return int
	 */
	protected function matchesNot($sStr)
	{
		$iPos = strpos($this->sCode, $sStr, $this->iOffset);

		if($iPos === false)
		{
			$iPos = $this->iCodeLen;
		}

		return $iPos > $this->iOffset ? $iPos - $this->iOffset : 0;
	}

	/**
	 * @param string $sSet
	 * @return int
	 */
	protected function matchesOneOf($sSet)
	{
		if($this->iOffset < $this->iCodeLen)
		{
			if(strpos($sSet, $this->sCode{$this->iOffset}) !== false)
			{
				return 1;
			}
		}

		return 0;
	}

	/**
	 * @param string $sSet
	 * @return int
	 */
	protected function matchesNotOneOf($sSet)
	{
		if($this->iOffset < $this->iCodeLen)
		{
			if(strpos($sSet, $this->sCode{$this->iOffset}) === false)
			{
				return 1;
			}
		}

		return 0;
	}

	/**
	 * @param string $sSet
	 * @return int
	 */
	protected function matchesManyOf($sSet)
	{
		return strspn($this->sCode, $sSet, $this->iOffset);
	}

	/**
	 * @param string $sSet
	 * @return int
	 */
	protected function matchesNotManyOf($sSet)
	{
		return strcspn($this->sCode, $sSet, $this->iOffset);
	}

	/**
	 * @param int $iLen
	 * @return void
	 */
	protected function consumeSilent($iLen)
	{
		$this->iOffset += $iLen;

		if($this->iOffset > $this->iFurthestOffset)
		{
			$this->iFurthestOffset = $this->iOffset;
		}
	}

	/**
	 * @param int $iLen
	 * @return string
	 */
	protected function consume($iLen)
	{
		$sStr = substr($this->sCode, $this->iOffset, $iLen);

		$this->consumeSilent($iLen);

		return $sStr;
	}

	/**
	 * @return void
	 */
	protected function consumeSilentAll()
	{
		return $this->consumeSilent($this->iCodeLen - $this->iOffset);
	}

	/**
	 * @return string
	 */
	protected function consumeAll()
	{
		return $this->consume($this->iCodeLen - $this->iOffset);
	}

	/**
	 * @return int
	 */
	protected function save()
	{
		$iIndex = $this->iSavePointCount++;

		$this->aSavePoints[$iIndex] = $this->iOffset;

		return $iIndex;
	}

	/**
	 * @param int $iSavePoint
	 * @return void
	 */
	protected function discard($iSavePoint)
	{
		if($iSavePoint < $this->iSavePointCount)
		{
			$this->iSavePointCount = $iSavePoint;
		}
	}

	/**
	 * @param int $iSavePoint
	 * @return void
	 */
	protected function restore($iSavePoint)
	{
		if($iSavePoint < $this->iSavePointCount)
		{
			$this->iOffset = $this->aSavePoints[$iSavePoint];

			$this->iSavePointCount = $iSavePoint;
		}
	}

	/**
	 * @param string $sError
	 * @return string
	 */
	protected function buildErrorMsg($sError)
	{
		$iLine = 1 + preg_match_all(
			"@(?:\r\n|\n\r|\r|\n)@",
			substr($this->sCode, 0, $this->iFurthestOffset),
			$aMatches,
			PREG_OFFSET_CAPTURE | PREG_SET_ORDER
		);

		$iByte = $this->iFurthestOffset;

		if($iLine > 1)
		{
			$aLast = $aMatches[$iLine - 2];

			$iByte = $this->iFurthestOffset - (
				$aLast[0][1] + strlen($aLast[0][0])
			);
		}

		$sSnippet = preg_replace(
			'@\\s+@', ' ', trim(substr(
				$this->sCode, $this->iFurthestOffset, 10
			))
		);

		return sprintf(
			'%s; line: %d, byte: %d, at: "%s"',
			$sError,
			$iLine,
			$iByte,
			addcslashes($sSnippet, '"')
		);
	}

	/**
	 * @return bool
	 */
	protected function reachedEnd()
	{
		return $this->iOffset >= $this->iCodeLen;
	}
}

abstract class etaBnfGeneratedParser extends etaBnfBasicParser
{
	/**
	 * @param string $sCode
	 * @return etaBnfTree
	 */
	public function parse($sCode)
	{
		$this->reset($sCode);

		$oParentNode = new etaBnfTreeNodeBranch(null);

		if($this->getMainNode($oParentNode) && $this->reachedEnd())
		{
			return new etaBnfTree($oParentNode->getSubNode(0));
		}

		throw new Exception($this->buildErrorMsg('cannot parse code'));
	}

	/**
	 * @param etaBnfTreeNodeBranch $oParentNode
	 * @return bool
	 */
	abstract protected function getMainNode(etaBnfTreeNodeBranch $oParentNode);
}

class etaBnfParserGenerator
{
	/**
	 * @var etaBnfDefStruct
	 */
	protected $oStruct;

	/**
	 * @var string
	 */
	protected $sParserClass;

	/**
	 * @var string
	 */
	protected $sParser;

	/**
	 * @var array
	 */
	protected $aGeneratedFunctions;

	/**
	 * @param string $sParserClass
	 * @param etaBnfDefStruct $oStruct
	 * @return void
	 */
	public function __construct($sParserClass, etaBnfDefStruct $oStruct)
	{
		$this->oStruct = $oStruct;
		$this->sParserClass = $sParserClass;
		$this->sParser = '';
		$this->aGeneratedFunctions = array();
	}

	/**
	 * @return string
	 */
	public function generateParser()
	{
		if(empty($sParser))
		{
			$this->buildParser();
		}

		return $this->sParser;
	}

	/**
	 * @return void
	 */
	protected function buildParser()
	{
		$oMainDef = $this->oStruct->getMainDef();

		$this->buildDef($oMainDef, false);

		$this->sParser = sprintf(
			'class %s extends etaBnfGeneratedParser{protected function ' .
				'getMainNode(etaBnfTreeNodeBranch$p){return$this->%s($p);}%s}',
			$this->sParserClass,
			$this->getDefFuncName($oMainDef, false),
			$this->sParser
		);
	}

	/**
	 * @param etaBnfDef $oDef
	 * @param bool $bNoTree
	 * @return string
	 */
	protected function buildDef(etaBnfDef $oDef, $bNoTree)
	{
		$sFuncName = $this->getDefFuncName($oDef, $bNoTree);

		if(isset($this->aGeneratedFunctions[$sFuncName]))
		{
			return;
		}

		$this->aGeneratedFunctions[$sFuncName] = true;

		$sTemplate = '$this->%s($b)&&%%s';

		$sComp = '%s';

		foreach($oDef->getComps() as $oComp)
		{
			$bWasTree = !$oComp->hasMod(etaBnfDefComp::MOD_NO_TREE);

			if($bNoTree && $bWasTree)
			{
				$oComp->addMod(etaBnfDefComp::MOD_NO_TREE);
			}

			$sComp = sprintf(
				$sComp, sprintf($sTemplate, $this->getCompFuncName($oComp))
			);

			$this->buildComp($oComp);

			if($bNoTree && $bWasTree)
			{
				$oComp->delMod(etaBnfDefComp::MOD_NO_TREE);
			}
		}

		$sComp = sprintf($sComp, 'true');

		$this->sParser .= sprintf(
			'protected function %s(etaBnfTreeNodeBranch$p){$b=new ' .
				'etaBnfTreeNodeBranch(%s);if(%s){%s}return false;}',
			$sFuncName,
			var_export($oDef->getName(), true),
			$sComp,
			$bNoTree ? 'return true;' : '$p->addSubNode($b);return true;'
		);
	}

	/**
	 * @param etaBnfDefComp $oComp
	 * @return void
	 */
	protected function buildComp(etaBnfDefComp $oComp)
	{
		$sFuncName = $this->getCompFuncName($oComp);

		if(isset($this->aGeneratedFunctions[$sFuncName]))
		{
			return;
		}

		$this->aGeneratedFunctions[$sFuncName] = true;

		if($oComp instanceof etaBnfDefCompRef)
		{
			$this->buildCompRef($oComp, $sFuncName);
		}
		elseif($oComp instanceof etaBnfDefCompSet)
		{
			$this->buildCompSet($oComp, $sFuncName);
		}
		elseif($oComp instanceof etaBnfDefCompStr)
		{
			$this->buildCompStr($oComp, $sFuncName);
		}
		else
		{
			throw new Exception('invalid definintion component');
		}
	}

	/**
	 * @param etaBnfDefCompRef $oRef
	 * @param string $sFuncName
	 * @return void
	 */
	protected function buildCompRef(etaBnfDefCompRef $oRef, $sFuncName)
	{
		$bNoTree = $oRef->hasMod(etaBnfDefComp::MOD_NO_TREE);

		switch($oRef->getQuant())
		{
			case etaBnfDefComp::QUANT_01:

				$sTemplate = '$s=$this->save();if($this->%s($p)){$this->' .
					'discard($s);return true;}$this->restore($s);%%s';

				$sContent = '%s';

				foreach($oRef->getRefs() as $sRef)
				{
					$oDef = $this->oStruct->getDef($sRef);

					$sContent = sprintf(
						$sContent,
						sprintf(
							$sTemplate,
							$this->getDefFuncName($oDef, $bNoTree)
						)
					);

					$this->buildDef($oDef, $bNoTree);
				}

				$sContent = sprintf($sContent, 'return true;');

				break;

			case etaBnfDefComp::QUANT_0N:

				$sTemplate = '$s=$this->save();if($this->%s($p)){$this->' .
					'discard($s);continue;}$this->restore($s);%%s';

				$sContent = 'while(true){%s}return true;';

				foreach($oRef->getRefs() as $sRef)
				{
					$oDef = $this->oStruct->getDef($sRef);

					$sContent = sprintf(
						$sContent,
						sprintf(
							$sTemplate,
							$this->getDefFuncName($oDef, $bNoTree)
						)
					);

					$this->buildDef($oDef, $bNoTree);
				}

				$sContent = sprintf($sContent, 'break;');

				break;

			case etaBnfDefComp::QUANT_1:

				$sTemplate = '$s=$this->save();if($this->%s($p)){$this->' .
					'discard($s);return true;}$this->restore($s);%%s';

				$sContent = '%s';

				foreach($oRef->getRefs() as $sRef)
				{
					$oDef = $this->oStruct->getDef($sRef);

					$sContent = sprintf(
						$sContent,
						sprintf(
							$sTemplate,
							$this->getDefFuncName($oDef, $bNoTree)
						)
					);

					$this->buildDef($oDef, $bNoTree);
				}

				$sContent = sprintf(
					$sContent, 'return false;'
				);

				break;

			case etaBnfDefComp::QUANT_1N:

				$sTemplate = '$s=$this->save();if($this->%s($p)){$this->' .
					'discard($s);continue;}$this->restore($s);%%s';

				$sContent = '$i=0;while(true){++$i;%s}return$i>1;';

				foreach($oRef->getRefs() as $sRef)
				{
					$oDef = $this->oStruct->getDef($sRef);

					$sContent = sprintf(
						$sContent,
						sprintf(
							$sTemplate,
							$this->getDefFuncName($oDef, $bNoTree)
						)
					);

					$this->buildDef($oDef, $bNoTree);
				}

				$sContent = sprintf($sContent, 'break;');

				break;

			default:

				throw new Exception('invalid definition component quantifier');
		}

		$this->sParser .= sprintf(
			'protected function %s(etaBnfTreeNodeBranch$p){%s}',
			$sFuncName,
			$sContent
		);
	}

	/**
	 * @param etaBnfDefCompSet $oSet
	 * @param string $sFuncName
	 * @return void
	 */
	protected function buildCompSet(etaBnfDefCompSet $oSet, $sFuncName)
	{
		$sUsedSet = var_export($oSet->getSet(), true);

		$sAdd = $oSet->hasMod(etaBnfDefComp::MOD_NO_TREE) ? '$this->' .
			'consumeSilent($l);' : '$p->addSubNode(new etaBnfTreeNodeLeaf(' .
			'$this->consume($l)));';

		switch($oSet->getQuant())
		{
			case etaBnfDefComp::QUANT_01:

				$sContent = sprintf(
					'static$s=%s;if(($l=$this->%s($s))>0){%s}return true;',
					$sUsedSet,
					$oSet->isNegated() ? 'matchesNotOneOf' : 'matchesOneOf',
					$sAdd
				);

				break;

			case etaBnfDefComp::QUANT_0N:

				$sContent = sprintf(
					'static$s=%s;if(($l=$this->%s($s))>0){%s}return true;',
					$sUsedSet,
					$oSet->isNegated() ? 'matchesNotManyOf' : 'matchesManyOf',
					$sAdd
				);

				break;

			case etaBnfDefComp::QUANT_1:

				$sContent = sprintf(
					'static$s=%s;if(($l=$this->%s($s))>0){%sreturn true;' .
						'}return false;',
					$sUsedSet,
					$oSet->isNegated() ? 'matchesNotOneOf' : 'matchesOneOf',
					$sAdd
				);


				break;

			case etaBnfDefComp::QUANT_1N:

				$sContent = sprintf(
					'static$s=%s;if(($l=$this->%s($s))>0){%sreturn true;' .
						'}return false;',
					$sUsedSet,
					$oSet->isNegated() ? 'matchesNotManyOf' : 'matchesManyOf',
					$sAdd
				);

				break;

			default:

				throw new Exception('invalid definition component quantifier');
		}

		$this->sParser .= sprintf(
			'protected function %s(etaBnfTreeNodeBranch$p){%s}',
			$sFuncName,
			$sContent
		);
	}

	/**
	 * @param etaBnfDefCompStr $oStr
	 * @param string $sFuncName
	 * @return void
	 */
	protected function buildCompStr(etaBnfDefCompStr $oStr, $sFuncName)
	{
		$sStr = $oStr->getStr();
		$iLen = strlen($sStr);

		$bNoTree = $oStr->hasMod(etaBnfDefComp::MOD_NO_TREE);
		$bNegated = $oStr->isNegated();

		if($iLen === 0)
		{
			if($bNegated)
			{
				$sContent = ($bNoTree ? '$this->consumeSilentAll();' : '$p->' .
					'addSubNode(new etaBnfTreeNodeLeaf($this->consumeAll()));').
					'return true;';
			}
			else
			{
				$sContent = ($bNoTree ? '' : '$p->addSubNode(new '.
					'etaBnfTreeNodeLeaf(\'\'));') . 'return true;';
			}
		}
		else
		{
			$sUsedStr = var_export($sStr, true);

			$sCheckFuncName = $bNegated ? 'matchesNot' : 'matches';

			switch($oStr->getQuant())
			{
				case etaBnfDefComp::QUANT_01:

					$sContent = sprintf(
						'static$s=%s;if(($l=$this->%s($s))>0){%s}' .
							'return true;',
						$sUsedStr,
						$sCheckFuncName,
						$bNoTree ? '$this->consumeSilent($l);' :
							'$p->addSubNode(new etaBnfTreeNodeLeaf($this->' .
							'consume($l)));'
					);

					break;

				case etaBnfDefComp::QUANT_0N:

					$sContent = sprintf(
						$bNoTree ? 'static$s=%s;while(($l=$this->%s($s))>0){'.
							'$this->consumeSilent($l);}return true;' :
							'static$s=%s;$t=\'\';while(($l=$this->%s($s))>0){' .
							'$t.=$this->consume($l);}if(strlen($t)>0){$p->' .
							'addSubNode(new etaBnfTreeNodeLeaf($t));}return' .
							'true;',
						$sUsedStr,
						$sCheckFuncName
					);

					break;

				case etaBnfDefComp::QUANT_1:

					$sContent = sprintf(
						'static$s=%s;if(($l=$this->%s($s))>0){%s' .
							'return true;}return false;',
						$sUsedStr,
						$sCheckFuncName,
						$bNoTree ? '$this->consumeSilent($l);' :
							'$p->addSubNode(new etaBnfTreeNodeLeaf($this->' .
							'consume($l)));'
					);

					break;

				case etaBnfDefComp::QUANT_1N:

					$sContent = sprintf(
						$bNoTree ? 'static$s=%s;$lt=0;while(($l=$this->%s($s' .
							'))>0){$lt+=$l;$this->consumeSilent($l);}return' .
							'$lt>0;' : 'static$s=%s;$t=\'\';while(($l=$this->' .
							'%s($s))>0){$t.=$this->consume($l);}if(strlen($t)' .
							'>0){$p->addSubNode(new etaBnfTreeNodeLeaf($t));'.
							'return true;}return false;',
						$sUsedStr,
						$sCheckFuncName
					);

					break;

				default:

					throw new Exception(
						'invalid definition component quantifier'
					);
			}
		}

		$this->sParser .= sprintf(
			'protected function %s(etaBnfTreeNodeBranch$p){%s}',
			$this->getCompFuncName($oStr),
			$sContent
		);
	}

	/**
	 * @param etaBnfDef $oDef
	 * @param bool $bNoTree
	 * @return string
	 */
	protected function getDefFuncName(etaBnfDef $oDef, $bNoTree)
	{
		return '_' . base_convert(crc32(
			sprintf('def%s%d', $oDef->getName(), (int) $bNoTree)
		), 10, 35);
	}

	/**
	 * @param etaBnfDefComp $oComp
	 * @return string
	 */
	protected function getCompFuncName(etaBnfDefComp $oComp)
	{
		if($oComp instanceof etaBnfDefCompRef)
		{
			return '_' . base_convert(crc32(sprintf(
				'ref%d%d%s',
				$oComp->getQuant(),
				$oComp->getMod(),
				implode(' ', $oComp->getRefs())
			)), 10, 35);
		}
		elseif($oComp instanceof etaBnfDefCompSet)
		{
			return '_' . base_convert(crc32(sprintf(
				'set%d%d%d%s',
				$oComp->getQuant(),
				$oComp->getMod(),
				$oComp->isNegated(),
				$oComp->getSet()
			)), 10, 35);
		}
		elseif($oComp instanceof etaBnfDefCompStr)
		{
			return '_' . base_convert(crc32(sprintf(
				'str%d%d%d%s',
				$oComp->getQuant(),
				$oComp->getMod(),
				$oComp->isNegated(),
				$oComp->getStr()
			)), 10, 35);
		}

		throw new Exception('invalid definintion component');
	}
}

?>
