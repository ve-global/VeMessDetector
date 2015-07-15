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
		
        $this->collectReturnStatements($node);
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
     * This method extracts all local variables for the given function or method node.
     *
     * @param AbstractNode $node
     */
    private function collectReturnStatements(AbstractNode $node)
    {
		$returns = $node->findChildrenOfType('ReturnStatement');
        foreach ($returns as $return) {
			if (
				!$this->isFinalReturn($return, $node) &&
				!$this->isAtEndOfIfStatement($return) &&
				!$this->isAtEndOfSwitchStatement($return) &&
				!$this->isAtEndOfTry($return) &&
				!$this->isAtEndOfCatch($return) &&
				!$this->isAtEndOfFinally($return) &&
				!$this->isAtEndOfClosure($return)
				) {
				$this->nodes[] = $return;
			}
		}
    }

	/**
	 * @param AbstractNode $node
	 * @return boolean
	 */
	private function isFinalReturn(AbstractNode $node, AbstractNode $method)
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
	 * @return boolean
	 */
	private function isFinalStatement(AbstractNode $scope, AbstractNode $node)
	{
		if ($node->getEndLine() + 1 === $scope->getEndLine() || $node->getEndLine() === $scope->getEndLine()) {
			return true;
		}

		return false;
	}
}
