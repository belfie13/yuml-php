<?php

namespace YumlPhp\Request\Http;

use TokenReflection\IReflectionClass;
use YumlPhp\Request\ClassesRequest as BaseRequest;

/**
 * HttpClassesRequest
 *
 * @author Robert Schönthal <seroscho@gmail.com>
 */
class ClassesRequest extends BaseRequest
{
    /**
     * @inheritDoc
     */
    public function build()
    {
        $request = array();
        foreach ($this->getClasses() as $class) {
            $this->addClass($class, $request);
            $this->addAssociations($class, $request);
        }

        natcasesort($request);

        return $request;
    }

    /**
     * @param IReflectionClass $class
     * @param array            $request
     */
    private function addClass(IReflectionClass $class, array &$request)
    {
        list($prefix, $suffix) = $this->determinePrefixAndSuffix($class);

        $parent = $this->buildParent($class, '[', ']^');
        $interfaces = $this->buildInterfaces($class, '<<', '>>{bg:orange}]^-.-[');
        $props = $this->buildProperties($class);
        $methods = $this->buildMethods($class);
        $pattern = $this->determinePattern($methods, $props);

        $line = sprintf($pattern, $parent, join(';', $interfaces), $this->buildName($class, $prefix, $suffix), join(';', $props), join(';', $methods));

        if ($class->isInterface()) {
            array_unshift($request, $line);
        } else {
            $request[] = $line;
        }
    }

    /**
     * @param IReflectionClass $class
     * @param array            $request
     */
    private function addAssociations(IReflectionClass $class, array &$request)
    {
        $usages = $this->buildUsages($class);

        if ($class->isInterface()) {
            return;
        }

        foreach ($usages as $usage) {
            list($prefix, $suffix) = $this->determinePrefixAndSuffix($usage);

            $request[] = sprintf('[%s]-.->[%s]', $this->buildName($class), $this->buildName($usage, $prefix, $suffix));
        }
    }

    /**
     * @param IReflectionClass $class
     * @return array
     */
    private function determinePrefixAndSuffix(IReflectionClass $class)
    {
        $prefix = null;
        $suffix = null;

        if ($class->isInterface() || substr($class->getName(), -strlen('Interface')) === 'Interface') {
            $suffix = '>>{bg:orange}';
            $prefix = '<<';
        } elseif ($class->isAbstract()) {
            $suffix = '{bg:blue}';
        }

        return array($prefix, $suffix);
    }

    /**
     * @param array $methods
     * @param array $props
     * @return string
     */
    private function determinePattern(array $methods, array $props)
    {
        $pattern = "%s[%s%s%s%s]";

        //rebuild pattern
        if (count($methods) || count($props)) {
            $pattern = "%s[%s%s|%s%s]";
        }
        if (count($props) && count($methods)) {
            $pattern = "%s[%s%s|%s|%s]";
        }

        return $pattern;
    }
}
