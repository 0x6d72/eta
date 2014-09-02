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

class etaAuxBnfTreeDump implements etaBnfTreeTraverseCallback
{
	/**
	 * @param etaBnfTreeNode $oNode
	 * @return void
	 */
	static public function dumpNode(etaBnfTreeNode $oNode)
	{
		self::dumpTree(new etaBnfTree($oNode));
	}

	/**
	 * @param etaBnfTree $oTree
	 * @return void
	 */
	static public function dumpTree(etaBnfTree $oTree)
	{
		$oTree->traverse(new self);
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePre()
	 */
	public function handleNodePre(etaBnfTreeNode $oNode, $iDepth)
	{
		printf("0x%02x ", $iDepth);

		if($iDepth > 0)
		{
			echo str_repeat(' .', $iDepth);
			echo ' ';
		}

		if($oNode instanceof etaBnfTreeNodeBranch)
		{
			echo $oNode->getName();
		}
		elseif($oNode instanceof etaBnfTreeNodeLeaf)
		{
			echo '"';
			echo addcslashes($oNode->getRawValue(), "\"'\0..\37\177..\377");
			echo '"';
		}

		echo PHP_EOL;
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePost()
	 */
	public function handleNodePost(etaBnfTreeNode $oNode, $iDepth)
	{
	}
}

class etaAuxMemoryDump
{
	/**
	 * @var array
	 */
	static protected $aOpcodeMapping = array(
		// generic
		etaOpcode::OP_NOP => 'NOP',
		etaOpcode::OP_HLT => 'HLT',
		etaOpcode::OP_SYS => 'SYS',
		etaOpcode::OP_SIZE => 'SIZE',

		// math
		etaOpcode::OP_ADD => 'ADD',
		etaOpcode::OP_SUB => 'SUB',
		etaOpcode::OP_MUL => 'MUL',
		etaOpcode::OP_DIV => 'DIV',

		// comparison
		etaOpcode::OP_EQ => 'EQ',
		etaOpcode::OP_LESS => 'LESS',
		etaOpcode::OP_AND => 'AND',
		etaOpcode::OP_OR => 'OR',

		// stack
		etaOpcode::OP_PUSH => 'PUSH',
		etaOpcode::OP_PUSHNIL => 'PUSHNIL',
		etaOpcode::OP_POP => 'POP',
		etaOpcode::OP_RPL => 'RPL',

		// jumps
		etaOpcode::OP_JMP => 'JMP',
		etaOpcode::OP_JT => 'JT',
		etaOpcode::OP_JF => 'JF',
		etaOpcode::OP_CALL => 'CALL',
		etaOpcode::OP_RET => 'RET',

		// i/o
		etaOpcode::OP_IN => 'IN',
		etaOpcode::OP_OUT => 'OUT',
		etaOpcode::OP_IOC => 'IOC',

		// table
		etaOpcode::OP_TAB => 'TAB',
		etaOpcode::OP_GET => 'GET',
		etaOpcode::OP_PUT => 'PUT',
		etaOpcode::OP_DEL => 'DEL',
		etaOpcode::OP_NXT => 'NXT',
		etaOpcode::OP_RES => 'RES',

		// string
		etaOpcode::OP_CONCAT => 'CONCAT'
	);

	/**
	 * @param etaVmMemory $oMemory
	 * @return void
	 */
	static public function dump(etaVmMemory $oMemory, $bNoInst = false)
	{
		$iCount = $oMemory->size();

		for($i=0;$i<$iCount;++$i)
		{
			$mValue = $oMemory->get($i);

			if($bNoInst && $mValue instanceof etaVmValueInst)
			{
				continue;
			}

			printf('%03d %s%s', $i, self::getStrValue($mValue), PHP_EOL);
		}
	}

	/**
	 * @param etaVmValue $mValue
	 * @return string
	 */
	static public function getStrValue(etaVmValue $oValue)
	{
		if($oValue instanceof etaVmValueInst)
		{
			if(isset(self::$aOpcodeMapping[$oValue->getValue()]))
			{
				$sOpcode = self::$aOpcodeMapping[$oValue->getValue()];
			}
			else
			{
				$sOpcode = 'unknown';
			}

			$sAddr = sprintf(
				$oValue->hasRelativeAddr() ? '(%s%d)' : ' %s%d',
				$oValue->hasIndirectAddr() ? ' ' : '#',
				$oValue->getAddr()
			);

			return sprintf('(i) %-10s %s', $sOpcode, $sAddr);
		}
		elseif($oValue instanceof etaVmValueInt)
		{
			return sprintf("(n) %d", $oValue->getValue());
		}
		elseif($oValue instanceof etaVmValueFloat)
		{
			return sprintf("(n) %f", $oValue->getValue());
		}
		elseif($oValue instanceof etaVmValueString)
		{
			return sprintf("(s) %s", $oValue->getValue());
		}
		elseif($oValue instanceof etaVmValueTable)
		{
			$aValues = array();

			$oValue->reset();
			$oValue->next($oIndex, $oSubValue);

			while(!($oIndex instanceof etaVmValueNil))
			{
				$aValues[] = sprintf(
					"%s = %s",
					self::getStrValue($oIndex),
					self::getStrValue($oSubValue)
				);

				$oValue->next($oIndex, $oSubValue);
			}

			return sprintf('(t) [%s]', implode(', ', $aValues));
		}
		elseif($oValue instanceof etaVmValueNil)
		{
			return sprintf("(v) nil");
		}

		return '(u) unknown';
	}
}

?>