<?php

/* home.twig */
class __TwigTemplate_d5daa3cd02c65ebcb87243563ae89e2eb3c757d36424a8ba2050527276def150 extends Twig_Template
{
    private $source;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "This is view test
";
    }

    public function getTemplateName()
    {
        return "home.twig";
    }

    public function getDebugInfo()
    {
        return array (  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "home.twig", "E:\\WAMP\\www\\authentication\\templates\\home.twig");
    }
}
