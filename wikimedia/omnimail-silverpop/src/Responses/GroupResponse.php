<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class GroupResponse extends BaseResponse {

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return GroupResponse
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getListID() {
        return $this->listID;
    }

    /**
     * @param mixed $listID
     *
     * @return GroupResponse
     */
    public function setListID($listID) {
        $this->listID = $listID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentListID() {
        return $this->parentListID;
    }

    /**
     * @param mixed $parentListID
     *
     * @return GroupResponse
     */
    public function setParentListID($parentListID): GroupResponse {
        $this->parentListID = $parentListID;
        return $this;
    }

    /**
     * Acoustic reference ID.
     *
     * @var int
     */
    protected $listID;

    /**
     * Acoustic parent reference ID.
     *
     * @var int
     */
    protected $parentListID;
}
