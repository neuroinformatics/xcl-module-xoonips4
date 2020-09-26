<?php

class Xoonips_Error
{
    private $msgId;
    private $isConst;
    private $fieldName;
    private $parameters = [];

    public function __construct($msgId, $fieldName, $parameters, $isConst)
    {
        $this->msgId = $msgId;
        $this->fieldName = $fieldName;
        $this->parameters = $parameters;
        $this->isConst = $isConst;
    }

    public function getMsgId()
    {
        return $this->msgId;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getMessage()
    {
        if ($this->isConst) {
            $ret = constant($this->msgId);
        } else {
            $ret = $this->msgId;
        }
        if (count($this->parameters) > 0) {
            $ret = XCube_Utils::formatString($ret, $this->parameters);
        }

        return $ret;
    }
}
