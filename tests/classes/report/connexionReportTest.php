<?php

/**
 * @group functional
 * @group legacy
 */
class report_connexionReportTest extends \report_abstractReportTestCase
{
    protected $ret;
    protected $dmin;
    protected $dmax;
    protected $report;
    protected $save_report;

    public function setUp()
    {
        parent::setUp();
        $date = new Datetime();
        $this->dmax = $date->format("Y-m-d H:i:s");
        $date->modify('-6 month');
        $this->dmin = $date->format("Y-m-d H:i:s");
        $databoxes = self::$DI['app']->getDataboxes();
        $this->ret = [];
        foreach ($databoxes as $databox) {
            $colls = $databox->get_collections();
            $rett = [];
            foreach ($colls as $coll) {
                $rett[$coll->get_coll_id()] = $coll->get_coll_id();
            }
            if (0 < count($rett)) {
                $this->ret[$databox->get_sbas_id()] = implode(',', $rett);
            }
        }
    }

    public function ColFilter()
    {
        $ret = $this->report->colFilter('user');

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $ret);
        foreach ($ret as $result) {
            $this->assertArrayHasKey('val', $result);
            $this->assertArrayHasKey('value', $result);
        }
    }

    public function testBuildReport()
    {
        $conf = [
            'user' => [self::$DI['app']['translator']->trans('phraseanet::utilisateurs'), 1, 1, 1, 1],
            'ddate' => [self::$DI['app']['translator']->trans('report:: date'), 1, 0, 1, 1],
            'ip' => [self::$DI['app']['translator']->trans('report:: IP'), 1, 0, 0, 0],
            'appli' => [self::$DI['app']['translator']->trans('report:: modules'), 1, 0, 0, 0],
            'fonction' => [self::$DI['app']['translator']->trans('report::fonction'), 1, 1, 1, 1],
            'activite' => [self::$DI['app']['translator']->trans('report::activite'), 1, 1, 1, 1],
            'pays' => [self::$DI['app']['translator']->trans('report::pays'), 1, 1, 1, 1],
            'societe' => [self::$DI['app']['translator']->trans('report::societe'), 1, 1, 1, 1]
        ];

        $nbResult = 0;
        foreach ($this->ret as $sbasid => $collections) {
            $this->report = new module_report_connexion(
                    self::$DI['app'],
                    $this->dmin,
                    $this->dmax,
                    $sbasid,
                    $collections
            );
            $this->ColFilter();
            $result = $this->report->buildReport($conf);

            if (count($result) > $nbResult)
                $this->save_report = $this->report;

            $nbResult = count($result);
            $this->reporttestPage($result);
            if (count($result['result']) > 0)
                $this->reporttestConf($conf);
            if (count($result['result']) > 0)
                $this->reporttestResult($result, $conf);
        }

        foreach ($this->ret as $sbasid => $collections) {
            $this->report = new module_report_connexion(
                    self::$DI['app'],
                    $this->dmin,
                    $this->dmax,
                    $sbasid,
                    $collections
            );

            $this->ColFilter();

            $result = $this->report->buildReport(false, 'user');
            $this->reporttestPage($result);
            if (count($result['result']) > 0)
                $this->reporttestConf($conf, 'user');
            if (count($result['result']) > 0)
                $this->reporttestResult($result, $conf, 'user');
        }

        $result = $this->save_report->buildReport(false, 'user');
    }

    public function reporttestPage($report)
    {
        $this->assertLessThanOrEqual($this->report->getNbRecord(), count($report['result']));

        $nbPage = $this->report->getTotal() / $this->report->getNbRecord();
        $nbPage = $nbPage < 1 ? 1 : $nbPage;

        if ($this->report->getTotal() > $this->report->getNbRecord())
            $this->assertTrue($report['display_nav']);
        else
            $this->assertFalse($report['display_nav']);

        if ($report['page'] == 1)
            $this->assertFalse($report['previous_page']);
        else
            $this->assertEquals($report['page'] - 1, $report['previous_page']);

        if (intval(ceil($nbPage)) == $report['page'])
            $this->assertFalse($report['next_page']);
        else
            $this->assertEquals($report['page'] + 1, $report['next_page']);
    }

    public function reporttestConf($conf, $groupby = false)
    {
        if ($groupby)
            $this->assertEquals(count($this->report->getDisplay()), 2);
        else
            $this->assertEquals(count($this->report->getDisplay()), count($conf));

        if (! $groupby) {
            foreach ($this->report->getDisplay() as $col => $colconf) {
                $this->assertArrayHaskey($col, $conf);
                $this->assertTrue(is_array($colconf));
                $this->assertArrayHasKey('title', $colconf);
                $this->assertArrayHasKey('sort', $colconf);
                $this->assertArrayHasKey('bound', $colconf);
                $this->assertArrayHasKey('filter', $colconf);
                $this->assertArrayHasKey('groupby', $colconf);
                $i = 0;
                foreach ($colconf as $key => $value) {
                    if ($i == 1)
                        $this->assertEquals($conf[$col][$i], $value);
                    elseif ($i == 2)
                        $this->assertEquals($conf[$col][$i], $value);
                    elseif ($i == 3)
                        $this->assertEquals($conf[$col][$i], $value);
                    elseif ($i == 4)
                        $this->assertEquals($conf[$col][$i], $value);
                    $i ++;
                }
            }
        } else {
            $this->assertArrayHasKey($groupby, $this->report->getDisplay());
            $this->assertArrayHasKey('nb', $this->report->getDisplay());
        }
    }

    public function reporttestResult($report, $conf, $groupby = false)
    {
        if (! $groupby) {
            foreach ($report['result'] as $row) {
                foreach ($conf as $key => $value) {

                    $this->assertArrayHasKey($key, $row);
                    $condition = is_string($row[$key]) || is_int($row[$key]);
                    $this->assertTrue($condition);
                }
            }
        } else {
            foreach ($report['result'] as $row) {
                $this->assertArrayHasKey($groupby, $row);
                $this->assertArrayHasKey('nb', $row);
            }
        }
    }

    public function reporttestResultWithChamp($report, $conf)
    {
        foreach ($report['result'] as $row) {
            foreach ($conf as $key => $value) {
                $this->assertArrayHasKey($value, $row);
                $condition = is_string($row[$value]);
                $this->assertTrue($condition);
            }
        }
    }
}
