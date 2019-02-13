<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "archive_calls_old".
 *
 * @property int $data_call_id
 * @property string $numv LNG_NUMV
 * @property string $ngod LNG_NGOD
 * @property string $ssmp
 * @property string $rsmp
 * @property string $prty LNG_PRTY
 * @property string $sect LNG_SECT
 * @property string $rjon LNG_RJON
 * @property string $city LNG_CITY
 * @property string $ulic LNG_ULIC
 * @property string $dom LNG_DOM
 * @property string $kvar LNG_KVAR
 * @property string $podz LNG_PODZ
 * @property string $etaj LNG_ETAJ
 * @property string $kodp LNG_KODP
 * @property string $telf LNG_TELF
 * @property string $mest LNG_MEST
 * @property string $comm LNG_COMM
 * @property string $povd LNG_POVD
 * @property string $fam LNG_FAM
 * @property string $imya LNG_IMYA
 * @property string $otch LNG_OTCH
 * @property string $vozr LNG_VOZR
 * @property string $pol LNG_POL
 * @property string $ktov LNG_KTOV
 * @property int $povt LNG_POVT
 * @property string $prof
 * @property string $smpt LNG_SMPT
 * @property string $stan LNG_STAN
 * @property string $dprm LNG_DPRM
 * @property string $tprm LNG_TPRM
 * @property string $wday LNG_WDAY
 * @property string $line LNG_LINE
 * @property string $rezl LNG_REZL
 * @property string $sgsp LNG_SGSP
 * @property string $rgsp LNG_SMPT
 * @property string $kuda LNG_KUDA
 * @property string $ds1 LNG_DS1
 * @property string $ds2 LNG_DS2
 * @property string $trav LNG_TRAV
 * @property string $alk LNG_ALK
 * @property string $numb LNG_NUMB
 * @property string $smpb LNG_SMPT
 * @property string $stbr LNG_SMPT
 * @property string $stbb LNG_SMPT
 * @property string $prfb LNG_PRFB
 * @property string $ncar LNG_NCAR
 * @property string $rcod LNG_RCOD
 * @property string $tabn LNG_TABN
 * @property string $dokt LNG_DOKT
 * @property string $tab2 LNG_TAB2
 * @property string $tab3 LNG_TAB3
 * @property string $tab4 LNG_TAB4
 * @property string $vr51 LNG_VR51
 * @property string $d201 LNG_D201
 * @property string $dsp1 LNG_DSP1
 * @property string $dsp2 LNG_DSP2
 * @property string $dspp LNG_DSPP
 * @property string $dsp3 LNG_DSP3
 * @property string $kakp LNG_KAKP
 * @property string $tper LNG_TPER
 * @property string $vyez LNG_VYEZ
 * @property string $przd LNG_PRZD
 * @property string $tgsp LNG_TGSP
 * @property string $tsta LNG_TSTA
 * @property string $tisp LNG_TISP
 * @property string $tvzv LNG_TVZV
 * @property string $kilo LNG_KILO
 * @property string $dlit LNG_DLIT
 * @property string $prdl LNG_PRD1
 * @property string $prib LNG_PRIB
 * @property string $prjn LNG_PRJN
 * @property string $pcty LNG_PCTY
 * @property string $pulc LNG_PULC
 * @property string $pdom LNG_PDOM
 * @property string $pkvr LNG_PKVR
 * @property string $kod1 LNG_KOD1
 * @property string $kod2 LNG_KOD2
 * @property string $plnk LNG_PLNK
 * @property string $poli LNG_POLI
 * @property string $spol LNG_SPOL
 * @property string $izv1 LNG_IZV1
 * @property string $tiz1 LNG_TIZ1
 * @property string $pri1 LNG_PRI1
 * @property string $izv2 LNG_IZV2
 * @property string $tiz2 LNG_TIZ2
 * @property string $pri2 LNG_PRI2
 * @property string $inf1 LNG_INF1
 * @property string $inf2 LNG_INF2
 * @property string $inf3 LNG_INF3
 * @property string $inf4 LNG_INF4
 * @property string $inf5 LNG_INF5
 * @property string $inf6 LNG_INF6
 * @property string $dshs LNG_DSHS
 * @property string $ferr LNG_FERR
 * @property string $expo LNG_EXPO
 * @property string $expo_str
 * @property string $mkb LNG_MKB
 * @property string $smpp LNG_SMPT
 * @property string $tree LNG_TREE
 * @property string $smbr LNG_SMPT
 * @property string $meds
 * @property string $medc_str LNG_MEDC
 * @property string $sost LNG__SOST
 * @property string $sost_code LNG_SOST_CODE
 * @property string $tbeg
 * @property string $acor
 * @property string $ucor
 * @property string $tend
 * @property string $subrf
 * @property string $tpovd
 * @property string $tdiag
 * @property string $kladv
 * @property string $kladp
 * @property string $lon
 * @property string $lat
 * @property string $exsys
 * @property string $exarm
 * @property string $exids
 * @property string $exidr
 * @property string $convert_flag
 * @property string $social_status
 * @property string $working_arterial_pressure_1
 * @property string $working_arterial_pressure_2
 * @property string $arterial_pressure_1
 * @property string $arterial_pressure_2
 * @property string $pulse_1
 * @property string $pulse_2
 * @property string $heart_rate_1
 * @property string $heart_rate_2
 * @property string $breathing_rate_1
 * @property string $breathing_rate_2
 * @property string $temperature_1
 * @property string $temperature_2
 * @property string $pulseoximetry_1
 * @property string $pulseoximetry_2
 * @property string $glucometry_1
 * @property string $glucometry_2
 */
class ArchiveCallsOld extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'archive_calls_old';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('adisdb');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numv', 'ngod', 'ssmp', 'prty', 'sect', 'rjon', 'city', 'ulic', 'dom', 'kvar', 'podz', 'etaj', 'kodp', 'telf', 'mest', 'povd', 'povt', 'smpt', 'stan', 'rezl', 'rgsp', 'ds1', 'ds2', 'trav', 'numb', 'smpb', 'stbr', 'stbb', 'prfb', 'ncar', 'tab2', 'tab3', 'tab4', 'vr51', 'd201', 'dsp1', 'dsp2', 'dspp', 'dsp3', 'prjn', 'pcty', 'pdom', 'kod1', 'plnk', 'poli', 'spol', 'izv1', 'izv2', 'dshs', 'expo', 'expo_str', 'smpp', 'smbr', 'medc_str', 'sost', 'sost_code', 'acor', 'ucor', 'subrf', 'tpovd', 'tdiag', 'kladv', 'kladp', 'lon', 'lat', 'exarm', 'exids'], 'required'],
            [['povt'], 'integer'],
            [['dprm', 'tprm', 'tper', 'vyez', 'przd', 'tgsp', 'tsta', 'tisp', 'tvzv', 'tiz1', 'tiz2', 'tbeg', 'tend', 'convert_flag'], 'safe'],
            [['expo_str', 'medc_str'], 'string'],
            [['numv', 'vozr', 'numb', 'rcod', 'kilo', 'dlit', 'expo'], 'string', 'max' => 4],
            [['ngod', 'lon', 'lat', 'exids', 'exidr', 'pulseoximetry_1', 'pulseoximetry_2', 'glucometry_1', 'glucometry_2'], 'string', 'max' => 10],
            [['ssmp', 'rsmp', 'kvar', 'povd', 'smpt', 'stan', 'smpb', 'stbr', 'stbb', 'tabn', 'tab2', 'tab3', 'tab4', 'vr51', 'd201', 'dsp1', 'dsp2', 'dspp', 'dsp3', 'pkvr', 'smpp', 'smbr', 'sost_code', 'ucor', 'heart_rate_1', 'heart_rate_2', 'breathing_rate_1', 'breathing_rate_2', 'temperature_1', 'temperature_2'], 'string', 'max' => 5],
            [['prty', 'mest', 'wday', 'line', 'alk', 'kakp', 'prib'], 'string', 'max' => 2],
            [['sect', 'rjon', 'podz', 'etaj', 'pol', 'prof', 'rezl', 'sgsp', 'rgsp', 'trav', 'prfb', 'prdl', 'prjn', 'pulse_1', 'pulse_2'], 'string', 'max' => 3],
            [['city', 'izv1', 'izv2'], 'string', 'max' => 30],
            [['ulic', 'kuda', 'pcty', 'sost'], 'string', 'max' => 50],
            [['dom', 'imya'], 'string', 'max' => 25],
            [['kodp'], 'string', 'max' => 11],
            [['telf'], 'string', 'max' => 13],
            [['comm'], 'string', 'max' => 85],
            [['fam', 'otch', 'ktov', 'tpovd', 'tdiag'], 'string', 'max' => 40],
            [['ds1', 'ds2', 'dshs', 'mkb', 'acor', 'subrf', 'exsys', 'exarm'], 'string', 'max' => 6],
            [['ncar', 'pdom'], 'string', 'max' => 15],
            [['dokt', 'inf1', 'inf2', 'inf3', 'inf4', 'inf5', 'inf6'], 'string', 'max' => 60],
            [['pulc'], 'string', 'max' => 38],
            [['kod1', 'kod2'], 'string', 'max' => 17],
            [['plnk', 'meds'], 'string', 'max' => 100],
            [['poli', 'spol', 'kladv', 'kladp'], 'string', 'max' => 20],
            [['pri1'], 'string', 'max' => 23],
            [['pri2'], 'string', 'max' => 19],
            [['ferr', 'tree'], 'string', 'max' => 1],
            [['social_status'], 'string', 'max' => 45],
            [['working_arterial_pressure_1', 'working_arterial_pressure_2', 'arterial_pressure_1', 'arterial_pressure_2'], 'string', 'max' => 7],
            [['dprm', 'ssmp', 'ngod'], 'unique', 'targetAttribute' => ['dprm', 'ssmp', 'ngod']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'data_call_id' => 'Data Call ID',
            'numv' => 'Numv',
            'ngod' => 'Ngod',
            'ssmp' => 'Ssmp',
            'rsmp' => 'Rsmp',
            'prty' => 'Prty',
            'sect' => 'Sect',
            'rjon' => 'Rjon',
            'city' => 'City',
            'ulic' => 'Ulic',
            'dom' => 'Dom',
            'kvar' => 'Kvar',
            'podz' => 'Podz',
            'etaj' => 'Etaj',
            'kodp' => 'Kodp',
            'telf' => 'Telf',
            'mest' => 'Mest',
            'comm' => 'Comm',
            'povd' => 'Povd',
            'fam' => 'Fam',
            'imya' => 'Imya',
            'otch' => 'Otch',
            'vozr' => 'Vozr',
            'pol' => 'Pol',
            'ktov' => 'Ktov',
            'povt' => 'Povt',
            'prof' => 'Prof',
            'smpt' => 'Smpt',
            'stan' => 'Stan',
            'dprm' => 'Dprm',
            'tprm' => 'Tprm',
            'wday' => 'Wday',
            'line' => 'Line',
            'rezl' => 'Rezl',
            'sgsp' => 'Sgsp',
            'rgsp' => 'Rgsp',
            'kuda' => 'Kuda',
            'ds1' => 'Ds1',
            'ds2' => 'Ds2',
            'trav' => 'Trav',
            'alk' => 'Alk',
            'numb' => 'Numb',
            'smpb' => 'Smpb',
            'stbr' => 'Stbr',
            'stbb' => 'Stbb',
            'prfb' => 'Prfb',
            'ncar' => 'Ncar',
            'rcod' => 'Rcod',
            'tabn' => 'Tabn',
            'dokt' => 'Dokt',
            'tab2' => 'Tab2',
            'tab3' => 'Tab3',
            'tab4' => 'Tab4',
            'vr51' => 'Vr51',
            'd201' => 'D201',
            'dsp1' => 'Dsp1',
            'dsp2' => 'Dsp2',
            'dspp' => 'Dspp',
            'dsp3' => 'Dsp3',
            'kakp' => 'Kakp',
            'tper' => 'Tper',
            'vyez' => 'Vyez',
            'przd' => 'Przd',
            'tgsp' => 'Tgsp',
            'tsta' => 'Tsta',
            'tisp' => 'Tisp',
            'tvzv' => 'Tvzv',
            'kilo' => 'Kilo',
            'dlit' => 'Dlit',
            'prdl' => 'Prdl',
            'prib' => 'Prib',
            'prjn' => 'Prjn',
            'pcty' => 'Pcty',
            'pulc' => 'Pulc',
            'pdom' => 'Pdom',
            'pkvr' => 'Pkvr',
            'kod1' => 'Kod1',
            'kod2' => 'Kod2',
            'plnk' => 'Plnk',
            'poli' => 'Poli',
            'spol' => 'Spol',
            'izv1' => 'Izv1',
            'tiz1' => 'Tiz1',
            'pri1' => 'Pri1',
            'izv2' => 'Izv2',
            'tiz2' => 'Tiz2',
            'pri2' => 'Pri2',
            'inf1' => 'Inf1',
            'inf2' => 'Inf2',
            'inf3' => 'Inf3',
            'inf4' => 'Inf4',
            'inf5' => 'Inf5',
            'inf6' => 'Inf6',
            'dshs' => 'Dshs',
            'ferr' => 'Ferr',
            'expo' => 'Expo',
            'expo_str' => 'Expo Str',
            'mkb' => 'Mkb',
            'smpp' => 'Smpp',
            'tree' => 'Tree',
            'smbr' => 'Smbr',
            'meds' => 'Meds',
            'medc_str' => 'Medc Str',
            'sost' => 'Sost',
            'sost_code' => 'Sost Code',
            'tbeg' => 'Tbeg',
            'acor' => 'Acor',
            'ucor' => 'Ucor',
            'tend' => 'Tend',
            'subrf' => 'Subrf',
            'tpovd' => 'Tpovd',
            'tdiag' => 'Tdiag',
            'kladv' => 'Kladv',
            'kladp' => 'Kladp',
            'lon' => 'Lon',
            'lat' => 'Lat',
            'exsys' => 'Exsys',
            'exarm' => 'Exarm',
            'exids' => 'Exids',
            'exidr' => 'Exidr',
            'convert_flag' => 'Convert Flag',
            'social_status' => 'Social Status',
            'working_arterial_pressure_1' => 'Working Arterial Pressure 1',
            'working_arterial_pressure_2' => 'Working Arterial Pressure 2',
            'arterial_pressure_1' => 'Arterial Pressure 1',
            'arterial_pressure_2' => 'Arterial Pressure 2',
            'pulse_1' => 'Pulse 1',
            'pulse_2' => 'Pulse 2',
            'heart_rate_1' => 'Heart Rate 1',
            'heart_rate_2' => 'Heart Rate 2',
            'breathing_rate_1' => 'Breathing Rate 1',
            'breathing_rate_2' => 'Breathing Rate 2',
            'temperature_1' => 'Temperature 1',
            'temperature_2' => 'Temperature 2',
            'pulseoximetry_1' => 'Pulseoximetry 1',
            'pulseoximetry_2' => 'Pulseoximetry 2',
            'glucometry_1' => 'Glucometry 1',
            'glucometry_2' => 'Glucometry 2',
        ];
    }
}
