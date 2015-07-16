<?php

namespace VeMessDetector\Rules;

use PHPMD\AbstractNode;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;

/**
 * This rule checks whether or not a local variable was ever defined inside the function.
 *
 * @author Nic Puddu <nicola.puddu@veinteractive.com>
 */
class UnreachableCode extends \PHPMD\AbstractRule implements FunctionAware, MethodAware
{

	/**
     * Collected ast nodes.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $nodes = [];

	/**
	 * @param AbstractNode $node
	 */
	public function apply(AbstractNode $node)
    {
        if ($this->isAbstractMethod($node)) {
            return;
        }
        
        $this->nodes = [];
		
        $this->collectStatements($node, 'ReturnStatement');
		$this->collectStatements($node, 'ThrowStatement');
        foreach ($this->nodes as $node) {
            $this->addViolation($node, array($node->getImage()));
        }
    }
	
	/**
     * Returns <b>true</b> when the given node is an abstract method.
     *
     * @param AbstractNode2 $node
     * @return boolean
     */
    private function isAbstractMethod(AbstractNode $node)
    {
        if ($node instanceof MethodNode) {
            return $node->isAbstract();
        }
        return false;
    }
   
    /**
     * This method extracts all statements of a given type for the given function or method node.
     *
     * @param AbstractNode $node
	 * @param string       $statementType
     */
    private function collectStatements(AbstractNode $node, $statementType)
    {
		$statements = $node->findChildrenOfType($statementType);
        foreach ($statements as $statement) {
			if (
				!$this->isAtEndOfMainScope($statement, $node) &&
				!$this->isAtEndOfIfStatement($statement) &&
				!$this->isAtEndOfSwitchStatement($statement) &&
				!$this->isAtEndOfTry($statement) &&
				!$this->isAtEndOfCatch($statement) &&
				!$this->isAtEndOfFinally($statement) &&
				!$this->isAtEndOfClosure($statement)
				) {
				$this->nodes[] = $statement;
			}
		}
    }

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfMainScope(AbstractNode $node, AbstractNode $method)
	{
		if ($this->isChildOf($node, 'Scope') && $this->isFinalStatement($method, $node)) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfIfStatement(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'ScopeStatement') &&
			(
				$this->isChildOf($node->getParent(), 'IfStatement') ||
				$this->isChildOf($node->getParent(), 'ElseIfStatement') ||
				$this->isChildOf($node->getParent(), 'ElseStatement')
			) &&
			$this->isFinalStatement($node->getParent(), $node)
			) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfSwitchStatement(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'SwitchLabel') &&
			$this->isChildOf($node->getParent(), 'SwitchStatement') &&
			$this->isFinalStatement($node->getParent(), $node, 0)
			) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfTry(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'ScopeStatement') &&
			$this->isChildOf($node->getParent(), 'TryStatement') &&
			$this->isFinalStatement($node->getParent(), $node) &&
			$this->thereIsNoFinallyStatement($node) &&
			$this->thereIsNoCodeBeyondTryCatch($node->getParent()->getParent())
			) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfCatch(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'ScopeStatement') &&
			$this->isChildOf($node->getParent(), 'CatchStatement') &&
			$this->isFinalStatement($node->getParent(), $node)
			) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfFinally(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'ScopeStatement') &&
			$this->isChildOf($node->getParent(), 'FinallyStatement') &&
			$this->isFinalStatement($node->getParent(), $node) &&
			$this->thereIsNoCodeBeyondTryCatch($node->getParent()->getParent()->getParent())
			) {
			return true;
		}

		return false;
	}

	/**
	 * @param AbstractNode $node
	 * @return type
	 */
	private function thereIsNoFinallyStatement(AbstractNode $node)
	{
		return $node->getParent()->getParent()->getFirstChildOfType('FinallyStatement') ? false : true;
	}

	/**
	 *
	 * @param AbstractNode $tryNode
	 * @return type
	 */
	private function thereIsNoCodeBeyondTryCatch(AbstractNode $tryNode)
	{
		return $this->isFinalStatement($tryNode->getParent(), $tryNode);
	}

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isAtEndOfClosure(AbstractNode $node)
	{
		if (
			$this->isChildOf($node, 'Scope') &&
			$node->getParent()->getParent() !== null &&
			$this->isChildOf($node->getParent(), 'Closure') &&
			$this->isFinalStatement($node->getParent(), $node)
			) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether or not a node is a children of a specific type.
	 *
	 * @param AbstractNode $node
	 * @param string $type
	 * @return boolean
	 */
	private function isChildOf(AbstractNode $node, $type)
	{
		$parent = $node->getParent();
        if ($parent->isInstanceOf($type)) {
            return true;
        }
        return false;
	}

	/**
	 * @param AbstractNode $scope
	 * @param AbstractNode $node
	 * @param integer      $adjustment number of lines to add to the final line of the node being checked.
	 * @return boolean
	 */
	private function isFinalStatement(AbstractNode $scope, AbstractNode $node, $adjustment = 1)
	{
		if ($node->getEndLine() + $adjustment === $scope->getEndLine()) {
			return true;
		}

		return false;
	}
}
