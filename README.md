eta
===

## What is eta?

eta (the esperanto word for small) is a small and flexible framework for prototyping domain specific languages. It features a parser generator, a compiler and a stack based virtual machine. It also includes the necessary tools to connect these three components.

To create a domain specific language everything that needs to be done is to define the language itself in a BNF like definition and to write a compiler that generates code based on the syntax tree.

The BNF definition is used to create a parser which is than used to create a syntax tree from the given source code. This Syntax tree is than used by the compiler to produce actual code (Bytecode for the VM or something else).

## Structure

eta is structured into several components which can be used separately. These components are bnf, compiler, vm and opcode and aux.

## Installing

To use eta in an existing project just include the files located in src/.
