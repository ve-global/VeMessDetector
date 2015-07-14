<?php

namespace VeMessDetector\Rules;

use PHPMD\AbstractNode;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;
use PHPMD\Rule\AbstractLocalVariable;

/**
 * Description of UndefinedLocalVariable
 *
 * @author nic
 */
class UndefinedLocalVariable extends AbstractLocalVariable implements FunctionAware, MethodAware
{

	/**
     * Collected ast nodes.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $nodes = array();

	public function apply(AbstractNode $node)
    {
        if ($this->isAbstractMethod($node)) {
            return;
        }
        
        $this->nodes = [];
        $this->collectVariables($node);
		$this->removeParameters($node);
        $this->removeDeclaredVariables($node);
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
    private function collectVariables(AbstractNode $node)
    {
		$variables = $node->findChildrenOfType('Variable');
        foreach ($variables as $variable) {
			if ($this->isLocal($variable) && !isset($this->nodes[$variable->getImage()])) {
				$this->nodes[$variable->getImage()] = $variable;
			}
			
        }
    }

	/**
     * This method removes from the stored list of local variables the ones that
     * are also found in the formal parameters of the given method or/and
     * function node.
     *
     * @param AbstractNode $node
     */
    private function removeParameters(AbstractNode $node)
    {
        // Get formal parameter container
        $parameters = $node->getFirstChildOfType('FormalParameters');
        // Now get all declarators in the formal parameters container
        $declarators = $parameters->findChildrenOfType('VariableDeclarator');
        foreach ($declarators as $declarator) {
            unset($this->nodes[$declarator->getImage()]);
        }
    }

	/**
     * This method removes from the stored list of local variables the ones that have been declared.
     *
     * @param AbstractNode $node
     */
    private function removeDeclaredVariables(AbstractNode $node)
    {
        foreach ($this->nodes as $variable) {
			if ($this->isChildOf($variable, 'AssignmentExpression') || $this->isChildOf($variable, 'ForeachStatement')) {
				unset($this->nodes[$variable->getImage()]);
			}

        }
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
}
