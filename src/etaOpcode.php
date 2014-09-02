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

class etaOpcode
{
	/**
	 * generic
	 * -------------------------------------------------------------------------
	 */

	/**
	 * NOP / -0 / +0
	 *
	 * does nothing and is meant to be a placeholder. the compiler uses it as
	 * jump or reference points at the end of blocks.
	 */
	const OP_NOP     = 0x00;

	/**
	 * HLT / -0 / +0
	 *
	 * halts execution of the virtual machine.
	 */
	const OP_HLT     = 0x01;

	/**
	 * SYS addr / -(v(addr)+1) / +1
	 *
	 * calls a systemfunction. requires the following stack layout:
	 * -(v(addr)+1) through -2 = parameters / where -(v(addr)+1) is the first
	 *                           and -2 the last parameter
	 * -1 = function name
	 * the address specification defines the number of parameters for the
	 * function call.
	 */
	const OP_SYS     = 0x02;

	/**
	 * SIZE addr / -0 / +1
	 *
	 * pushes the size of v(addr) onto the stack. for tables this is the number
	 * of elements in the table and for the strings the length of the string.
	 * for all other values a size of 0 is assumed.
	 */
	const OP_SIZE    = 0x03;

	/**
	 * math
	 * -------------------------------------------------------------------------
	 */

	/**
	 * ADD / -2 / +1
	 *
	 * adds v(-2) and v(-1) removes -1 and -2 from the stack and pushes the
	 * result onto the stack.
	 */
	const OP_ADD     = 0x04;

	/**
	 * SUB / -2 / +1
	 *
	 * subtracts v(-2) and v(-1) removes -1 and -2 from the stack and pushes the
	 * result onto the stack.
	 */
	const OP_SUB     = 0x05;

	/**
	 * MUL / -2 / +1
	 *
	 * multiplies v(-2) and v(-1) removes -2 and -1 from the stack and pushes
	 * the result onto the stack.
	 */
	const OP_MUL     = 0x06;

	/**
	 * DIV / -2 / +1
	 *
	 * divides v(-2) and v(-1) removes -1 and -2 from the stack and pushes the
	 * result onto the stack.
	 */
	const OP_DIV     = 0x07;

	/**
	 * comparison
	 * -------------------------------------------------------------------------
	 */

	/**
	 * EQ / -2 / +1
	 *
	 * tests whether v(-1) and v(-2) are equal. -1 and -2 will be removed from
	 * the stack. if the two values are equal 1 otherwise 0 is pushed onto the
	 * stack.
	 */
	const OP_EQ      = 0x08;

	/**
	 * LESS / -2 / +1
	 *
	 * tests whether v(-2) is less than v(-1). -1 and -2 will be removed from
	 * the stack. if v(-2) is less than v(-1) 1 otherwise 0 is pushed onto the
	 * stack.
	 */
	const OP_LESS    = 0x09;

	/**
	 * AND / -2 / +1
	 *
	 * checks whether v(-1) and v(-2) both contain 1. if that is the case then
	 * 1 is pushed onto the stack. otherwise 0 is pushed onto the stack.
	 */
	const OP_AND     = 0x0A;

	/**
	 * OR / -2 / +1
	 *
	 * checks whether v(-1) or v(-2) contain 1. if that is the case then 1 is
	 * pushed onto the stack. otherwise 0 is pushed onto the stack.
	 */
	const OP_OR      = 0x0B;

	/**
	 * stack
	 * -------------------------------------------------------------------------
	 */

	/**
	 * PUSH addr / -0 / +1
	 *
	 * pushes v(addr) onto the stack.
	 */
	const OP_PUSH    = 0x0C;

	/**
	 * PUSHNIL / -0 / +1
	 *
	 * pushes nil onto the stack.
	 */
	const OP_PUSHNIL = 0x0D;

	/**
	 * POP addr / -v(addr) / +0
	 *
	 * removes v(addr) elements from the stack
	 */
	const OP_POP     = 0x0E;

	/**
	 * RPL addr / -1 / +0
	 *
	 * replaces v(v(addr)) with v(-1). -1 will be removed from the stack.
	 */
	const OP_RPL     = 0x0F;

	/**
	 * jumps
	 * -------------------------------------------------------------------------
	 */

	/**
	 * JMP addr / -0 / +0
	 *
	 * jumps to the stack entry at v(addr).
	 */
	const OP_JMP     = 0x10;

	/**
	 * JT addr / -1 / +0
	 *
	 * jumps to stack entry v(addr) if v(-1) has the value 1. removes -1 from
	 * the stack.
	 */
	const OP_JT      = 0x11;

	/**
	 * JT addr / -1 / +0
	 *
	 * jumps to stack entry v(addr) if v(-1) has the value 0. removes -1 from
	 * the stack.
	 */
	const OP_JF      = 0x12;

	/**
	 * CALL addr / -(v(addr)+1) / +0
	 *
	 * executes the instructions located at v(-1) with the values -(v(addr)+1)
	 * through -2 on top of the stack. the instruction pointer and the frame
	 * pointer are stored just below the parameters on the stack.
	 *
	 * this instruction requires the following stack layout:
	 *  -(v(addr)+1) through -2 = parameters / where -(v(addr)+1) is the first
	 *                            and -2 the last parameter
	 *  -1 = new execution location (instruction pointer)
	 *  this stack layout is the same as the one of OP_SYS.
	 */
	const OP_CALL    = 0x13;

	/**
	 * RET / -0 / +1
	 *
	 * returns from a previous OP_CALL. resets the stack to what is was before
	 * the OP_CALL instruction without the parameters and restores the frame and
	 * instruction pointer. -1 is always considered to be the return value and
	 * is pushed separately onto the stack.
	 */
	const OP_RET     = 0x14;

	/**
	 * i/o
	 * -------------------------------------------------------------------------
	 */

	/**
	 * IN / -0 / +1
	 *
	 * reads data from the i/o interface and pushes the result onto the stack.
	 */
	const OP_IN      = 0x15;

	/**
	 * OUT addr / -0 / +0
	 *
	 * writes v(addr) into the i/o interface. the value is left untouched.
	 */
	const OP_OUT     = 0x16;

	/**
	 * IOC addr / -0 / +1
	 *
	 * writes v(addr) as a control value into the i/o interface. a result or nil
	 * will pushed onto the stack.
	 */
	const OP_IOC     = 0x17;

	/**
	 * table
	 * -------------------------------------------------------------------------
	 */

	/**
	 * TAB addr / -v(addr) / +1
	 *
	 * creates a new table with v(addr) elements from the stack. these elements
	 * are stored in the table and then removed from the stack. the new table
	 * will be pushed onto the stack.
	 */
	const OP_TAB    = 0x18;

	/**
	 * GET addr / -1 / +1
	 *
	 * pushes v(addr)[v(-1)] onto the stack. the index element -1 will be
	 * removed from the stack.
	 */
	const OP_GET     = 0x19;

	/**
	 * PUT addr / -2 / +0
	 *
	 * replaces the value v(addr)[v(-2)] with v(-1). -2 and -1 will be removed
	 * from the stack.
	 */
	const OP_PUT    = 0x1A;

	/**
	 * DEL addr / -1 / +0
	 *
	 * removes the value with the index v(-1) from the table v(addr). -1 will be
	 * removed from the stack.
	 */
	const OP_DEL     = 0x1B;

	/**
	 * NXT addr / -0 / +2
	 *
	 * pushes the current index/value pair onto the stack and increments the
	 * internal pointer of the table referenced by v(addr). the index will be
	 * at -2 and the value is at -1.
	 */
	const OP_NXT     = 0x1C;

	/**
	 * RES addr / -0 / +0
	 *
	 * resets the internal pointer of the table referenced by v(addr).
	 */
	const OP_RES     = 0x1D;

	/**
	 * string
	 * -------------------------------------------------------------------------
	 */

	/**
	 * CONCAT addr / -v(addr) / +1
	 *
	 * concatenates v(addr) elements from the stack to one single element. the
	 * resulting value is a string.
	 */
	const OP_CONCAT  = 0x1E;
}

?>