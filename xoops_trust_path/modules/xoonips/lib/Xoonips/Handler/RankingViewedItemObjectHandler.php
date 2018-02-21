<?php

namespace Xoonips\Handler;

use Xoonips\Core\Functions;

/**
 * ranking viewed item object handler.
 */
class RankingViewedItemObjectHandler extends AbstractObjectHandler
{
    /**
     * event type id.
     */
    const ETID_VIEW_ITEM = 7;

    /**
     * constructor.
     *
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_ranking_viewed_item');
        $this->mPrimaryKey = 'item_id';
    }

    /**
     * update viewed rankings.
     *
     * @param int term
     *
     * @return bool
     */
    public function update($term)
    {
        // delete old data
        if (!$this->deleteAll(null, true)) {
            return false;
        }
        // update viewed ranking
        return $this->_recalc($term);
    }

    /**
     * recalc.
     *
     * @param int term
     *
     * @return bool
     */
    private function _recalc($term)
    {
        $eventLogHandler = Functions::getXoonipsHandler('EventLogObject', $this->mDirname);
        $fieldlist = 'item_id, COUNT(*) AS count';
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('event_type_id', self::ETID_VIEW_ITEM));
        $criteria->add(new \Criteria('timestamp', $term, '>='));
        $criteria->setGroupby('item_id');
        $criteria->setSort('count', 'DESC');
        if (!$res = $eventLogHandler->open($criteria, $fieldlist)) {
            return false;
        }
        while ($obj = $eventLogHandler->getNext($res)) {
            $item_id = $obj->get('item_id');
            $count = $obj->getExtra('count');
            $ranking = $this->create();
            $ranking->set('item_id', $item_id);
            $ranking->set('count', $count);
            $this->insert($ranking, true);
        }
        $eventLogHandler->close($res);

        return true;
    }
}
