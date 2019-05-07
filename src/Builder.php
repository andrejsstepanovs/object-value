<?php

declare(strict_types = 1);

namespace ObjectValue;

use PhpParser\Builder\Method;
use PhpParser\BuilderFactory;
use PhpParser\NodeAbstract;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Doctrine\Common\Inflector\Inflector;

/**
 * Class Builder
 * @package ObjectValue
 */
class Builder
{
    /** @var array */
    private $config = [];

    /** @var Inflector */
    private $inflector;

    /** @var Finder */
    private $finder;

    /**
     * Builder constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config['entities_namespace'] = $config['entities_namespace'];
        $this->config['entities_path']      = $config['entities_path'];
        $this->config['interface_path']     = $config['interface_path'];
    }

    /**
     * @param string $interface
     * @return array
     */
    public function getFromInterface(string $interface)
    {
        $entityInterfaceName = $this->getName($interface);
        $entityName = str_replace('Interface', '', $entityInterfaceName);

        $entityFullName = $this->config['entities_namespace'].'\\'.$entityName;
        if (class_exists($entityFullName)) {
            return new $entityFullName();
        }

        $interfaceFile = $this->findInterface($entityInterfaceName);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($interfaceFile->getContents());

        $methods = $this->findMethods($ast);

        $uses = $this->findUse($ast);
        $uses[__NAMESPACE__.'\Entity'] = 'AbstractEntity';
        $uses[$interface] = 'EntityInterface';

        $newAst = $this->create($entityName, $uses, $methods);

        $entityFile = $this->config['entities_path'].'/'.$entityName.'.php';

        $content = (new Standard())->prettyPrintFile($newAst);
        $success = file_put_contents($entityFile, $content);
        if ($success === false) {
            throw new \RuntimeException('Failed to write file "'.$entityFile.'"');
        }

        require_once $entityFile;

        return new $entityFullName();
    }

    /**
     * @param string $name
     * @return string
     */
    private function getName(string $name): string
    {
        $paths = explode('\\', $name);
        return array_pop($paths);
    }

    /**
     * @param $name
     * @return SplFileInfo
     */
    private function findInterface($name): SplFileInfo
    {
        $interfaceFileName = $name.'.php';

        $files = $this->getFinder()->in('.')->name($interfaceFileName)->files();
        $foundFiles = [];
        foreach ($files as $file) {
            $foundFiles[] = $file;
        }

        // check if actually is interface
        foreach ($foundFiles as $file) {
            return $file;
        }

        throw new \RuntimeException('Interface not found');
    }

    /**
     * @param array $ast
     * @return array
     */
    private function findUse(array $ast): array
    {
        $uses = [];
        foreach ($ast as $tmtp) {
            if (!$tmtp instanceof Namespace_) {
                continue;
            }
            foreach ($tmtp->stmts as $definitions) {
                if (!$definitions instanceof Use_) {
                    continue;
                }
                foreach ($definitions->uses as $use) {
                    $alias = isset($use->alias) ? $use->alias->name : '';
                    $uses[implode('\\', $use->name->parts)] = $alias;
                }
            }
        }

        return $uses;
    }

    /**
     * @param array $ast
     * @return array
     */
    private function findMethods(array $ast): array
    {
        $methods = [];
        foreach ($ast as $tmtp) {
            if (!$tmtp instanceof Namespace_) {
                continue;
            }
            foreach ($tmtp->stmts as $definitions) {
                if (!isset($definitions->stmts)) {
                    continue;
                }
                foreach ($definitions->stmts as $method) {
                    if (!$method instanceof ClassMethod) {
                        continue;
                    }
                    $params = [];
                    foreach ($method->params as $param) {
                        $par = $this->getTypeData($param->type);
                        if (isset($param->default)) {
                            $par['default'] = $param->default->value;
                        }
                        $params[] = ['name' => $param->var->name] + $par;
                    }

                    $methods[] = [
                        'name'   => $method->name->name,
                        'params' => $params,
                        'return' => $this->getTypeData($method->returnType),
                    ];
                }
            }
        }

        return $methods;
    }

    /**
     * @param null|NodeAbstract $type
     * @return array
     */
    private function getTypeData(?NodeAbstract $type): array
    {
        $typeStr = '';
        $nullable = false;

        if ($type instanceof NullableType) {
            $nullable = true;
            if ($type->type instanceof Name) {
                foreach ($type->type->parts as $part) {
                    $typeStr = $part;
                }
            }
            if ($type->type instanceof FullyQualified) {
                foreach ($type->type->parts as $part) {
                    $typeStr = '\\'.$part;
                }
            }
            if ($type->type instanceof Identifier) {
                $typeStr = $type->type->name;
            }
        }
        if ($type instanceof Identifier) {
            $typeStr = $type->name;
        }

        if ($type instanceof Name) {
            foreach ($type->parts as $part) {
                $typeStr = $part;
            }
        }
        if ($type instanceof FullyQualified) {
            foreach ($type->parts as $part) {
                $typeStr = '\\'.$part;
            }
        }

        return ['type' => $typeStr, 'nullable' => $nullable];
    }

    /**
     * @param string $entityName
     * @param array  $uses
     * @param array  $methods
     * @return array
     */
    private function create(string $entityName, array $uses, array $methods)
    {
        $factory = new BuilderFactory;

        $comment = [
            '/**',
            ' * Class '.$entityName,
            ' * @package '.$this->config['entities_namespace'],
            ' */'
        ];

        $class = $factory
            ->class($entityName)
            ->setDocComment(implode(PHP_EOL, $comment))
            ->extend('AbstractEntity')
            ->implement('EntityInterface')
        ;
        foreach ($methods as $method) {
            $count  = count($method['params']);
            $name   = substr($method['name'], 3);
            $return = $method['return'];

            if ($count == 1) {
                $param = array_pop($method['params']);
                $type  = ($param['nullable'] ? '?' : '').$param['type'];
                $class->addStmt($this->getSetterMethod($factory, $name, $type, $param['nullable']));
            }
            if ($count == 0) {
                $class->addStmt($this->getGetterMethod($factory, $name, $return['type'], $return['nullable']));
            }
        }

        $fact = $factory->namespace($this->config['entities_namespace']);

        foreach ($uses as $key => $alias) {
            $stmt = $factory->use($key);
            if (!empty($alias)) {
                $stmt->as($alias);
            }
            $fact->addStmt($stmt);
        }

        $fact->addStmt($class);

        $strict = new Declare_(
            [new DeclareDeclare(new Identifier('strict_types'), new LNumber(1, ['kind' => LNumber::KIND_DEC]))]
        );

        return [$strict, $fact->getNode()];
    }

    /**
     * @param BuilderFactory $factory
     * @param string         $argument
     * @param string         $type
     * @param bool           $nullable
     * @return Method
     */
    private function getSetterMethod(BuilderFactory $factory, string $argument, string $type, bool $nullable = true): Method
    {
        $inflector = $this->getInflector();
        $name    = $inflector->tableize($argument);
        $nameCam = $inflector->camelize($argument);
        $nameCas = $inflector->classify($argument);

        $prettyPrinter = new Standard();
        $comment = [
            '/**',
            ' * @param '.($nullable ? 'null|' : '').''.$type.' '.$prettyPrinter->prettyPrintExpr((new Variable($nameCam))),
            ' * @return '.$prettyPrinter->prettyPrintExpr((new Variable('this'))),
            ' */'
        ];

        $method = $factory
            ->method('set'.$nameCas)
            ->makePublic()
            ->setReturnType('self')
            ->addParam($factory->param($nameCam)->setType(($nullable ? '?' : '').$type))
            ->setDocComment(implode(PHP_EOL, $comment))
            ->addStmt($factory->methodCall(new Variable('this'), 'set', [$name, new Variable($nameCam)]))
            ->addStmt(new Return_(new Variable('this')))
        ;

        return $method;
    }

    /**
     * @param BuilderFactory $factory
     * @param string         $argument
     * @param string         $type
     * @param bool           $nullable
     * @return Method
     */
    private function getGetterMethod(BuilderFactory $factory, string $argument, string $type, bool $nullable = true): Method
    {
        $inflector = $this->getInflector();
        $name    = $inflector->tableize($argument);
        $nameCas = $inflector->classify($argument);

        $comment = [
            '/**',
            ' * @return '.($nullable ? 'null|' : '').''.$type,
            ' */'
        ];

        $method = $factory
            ->method('get'.$nameCas)
            ->makePublic()
            ->setReturnType(($nullable ? '?' : '').$type)
            ->setDocComment(implode(PHP_EOL, $comment))
            ->addStmt(new Return_($factory->methodCall(new Variable('this'), 'get', [$name])))
        ;

        return $method;
    }


    /**
     * @return Inflector
     */
    private function getInflector(): Inflector
    {
        if ($this->inflector === null) {
            $this->inflector = new Inflector();
        }

        return $this->inflector;
    }

    /**
     * @return Finder
     */
    private function getFinder(): Finder
    {
        if ($this->finder === null) {
            $this->finder = new Finder();
        }

        return $this->finder;
    }
}