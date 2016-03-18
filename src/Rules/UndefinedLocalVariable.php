<?php

namespace VeMessDetector\Rules;

use PHPMD\AbstractNode;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;
use PHPMD\Rule\AbstractLocalVariable;

/**
 * This rule checks whether or not a local variable was ever defined inside the function.
 *
 * @author Nic Puddu <nicola.puddu@veinteractive.com>
 */
class UndefinedLocalVariable extends AbstractLocalVariable implements FunctionAware, MethodAware
{

    /**
     * Collected ast nodes.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $nodes = [];

    /**
     * Collected closures.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $closures = [];

    /**
     * Current Closure analysed.
     *
     * @var \PHPMD\Node\ASTNode[]
     */
    private $currentClosure;

    /**
     * @param AbstractNode $node
     */
    public function apply(AbstractNode $node)
    {
        if ($this->isAbstractMethod($node)) {
            return;
        }

        $this->nodes = [];
        $this->closures = [];
        $this->currentClosure = null;

        $this->getClosures($node);
        $this->collectVariables($node);
        $this->removeParameters($node);
        $this->removeDeclaredVariables();
        $this->removePregMatchVariables();
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
     * Gets closures within the method or function.
     *
     * @param AbstractNode $node
     */
    private function getClosures(AbstractNode $node)
    {
        $closures = $node->findChildrenOfType('Closure');

        foreach ($closures as $closure) {
            $this->closures[] = $closure;
        }
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
            if ($this->isLocal($variable) && $this->isNotInClosure($variable) && !isset($this->nodes[$variable->getImage()])) {
                $this->nodes[$variable->getImage()] = $variable;
            }

        }
    }

    /**
     * Checks whether or not the current variable is inside a closure.
     *
     * @param AbstractNode $node
     * @return boolean
     */
    private function isNotInClosure(AbstractNode $node)
    {
        $variableLine = $node->getBeginLine();
        foreach ($this->closures as $closure) {
            if ($closure !== $this->currentClosure && $variableLine > $closure->getBeginLine() && $variableLine < $closure->getEndLine()) {
                return false;
            }
        }

        return true;
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
     */
    private function removeDeclaredVariables()
    {
        foreach ($this->nodes as $variable) {
            if (
                $this->isChildOf($variable, 'AssignmentExpression') ||
                $this->isChildOf($variable, 'ForeachStatement') ||
                $this->isChildOf($variable, 'CatchStatement') ||
                $this->isChildOf($variable, 'ListExpression') ||
                (
                    $this->isChildOf($variable, 'UnaryExpression') &&
                    $this->isChildOf($variable->getParent(), 'ForeachStatement')
                ))  {
                unset($this->nodes[$variable->getImage()]);
            }

        }
    }

    /**
     * Remove from the stored list of local variables the third argument of preg_match
     * and preg_match_all functions.
     */
    private function removePregMatchVariables()
    {
        if ($this->getBooleanProperty('ignorePregMatch') !== true)
        {
            return;
        }

        foreach ($this->nodes as $variable) {
            $parent = $variable->getParent()->getParent();
            if ($parent->isInstanceOf('FunctionPostfix') && in_array($this->getFunctionShortName($parent->getImage()), ['preg_match', 'preg_match_all'])) {
                $this->discardPregMatchThirdArgument($parent, $variable);
            }
        }
    }

    /**
     * @param AbstractNode $function
     * @param AbstractNode $variable
     */
    private function discardPregMatchThirdArgument(AbstractNode $function, AbstractNode $variable)
    {
        $arguments = $function->findChildrenOfType('Arguments')[0];
        if (count($arguments->getChildren()) < 3)
        {
            return;
        }
        $parameter = $arguments->getChild(2);
        if ($parameter == $variable)
        {
            unset($this->nodes[$variable->getImage()]);
        }
    }

    /**
     * @param string $functionName
     * @return string
     */
    private function getFunctionShortName($functionName)
    {
        $lastSlashPosition = strrpos($functionName, '\\');
        if ($lastSlashPosition === false)
        {
            return $functionName;
        }
        return substr($functionName, $lastSlashPosition + 1);
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
