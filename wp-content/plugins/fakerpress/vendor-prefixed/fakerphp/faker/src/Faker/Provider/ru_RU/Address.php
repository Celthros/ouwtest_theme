<?php

namespace FakerPress\ThirdParty\Faker\Provider\ru_RU;

class Address extends \FakerPress\ThirdParty\Faker\Provider\Address
{
    protected static $cityPrefix = ['город'];

    protected static $regionSuffix = ['область'];
    protected static $streetPrefix = [
        'пер.', 'ул.', 'пр.', 'шоссе', 'пл.', 'бульвар',
        'въезд', 'спуск', 'проезд', 'наб.',
    ];

    protected static $buildingNumber = ['%#'];
    protected static $postcode = ['######'];

    /**
     * @see https://ru.wikipedia.org/wiki/Общероссийский_классификатор_стран_мира#Список_стран_согласно_Классификатору
     */
    protected static $country = [
        'Абхазия', 'Австралия', 'Австрия', 'Азербайджан', 'Албания', 'Алжир', 'Американское Самоа', 'Ангилья', 'Ангола', 'Андорра', 'Антарктида', 'Антигуа и Барбуда', 'Аргентина', 'Армения', 'Аруба', 'Афганистан',
        'Багамы', 'Бангладеш', 'Барбадос', 'Бахрейн', 'Беларусь', 'Белиз', 'Бельгия', 'Бенин', 'Бермуды', 'Болгария', 'Боливия', 'Бонэйр, Синт-Эстатиус и Саба', 'Босния и Герцеговина', 'Ботсвана', 'Бразилия', 'Британская Территория в Индийском Океане', 'Британские Виргинские Острова', 'Бруней', 'Буркина-Фасо', 'Бурунди', 'Бутан',
        'Вануату', 'Ватикан', 'Венгрия', 'Венесуэла', 'Великобритания', 'Виргинские Острова Соединённых Штатов', 'Вьетнам',
        'Габон', 'Гаити', 'Гайана', 'Гамбия', 'Гана', 'Гваделупа', 'Гватемала', 'Гвинея', 'Гвинея-Бисау', 'Германия', 'Гернси', 'Гибралтар', 'Гондурас', 'Гонконг', 'Гренада', 'Гренландия', 'Греция', 'Грузия', 'Гуам',
        'Дания', 'Демократическая Республика Конго', 'Джерси', 'Джибути', 'Доминика', 'Доминиканская Республика',
        'Египет',
        'Замбия', 'Западная Сахара', 'Зимбабве',
        'Израиль', 'Индия', 'Индонезия', 'Иордания', 'Ирак', 'Иран', 'Ирландия', 'Исландия', 'Испания', 'Италия',
        'Йемен',
        'Кабо-Верде', 'Казахстан', 'Камбоджа', 'Камерун', 'Канада', 'Катар', 'Кения', 'Кипр', 'Киргизия', 'Кирибати', 'Китай', 'Кокосовые острова', 'Колумбия', 'Коморы', 'Конго', 'Корейская Народно-Демократическая Республика', 'Корея', 'Коста-Рика', 'Кот-д\'Ивуар', 'Куба', 'Кувейт', 'Кюрасао',
        'Лаос', 'Латвия', 'Лесото', 'Либерия', 'Ливан', 'Ливия', 'Литва', 'Лихтенштейн', 'Люксембург',
        'Маврикий', 'Мавритания', 'Мадагаскар', 'Майотта', 'Макао', 'Малави', 'Малайзия', 'Мали', 'Малые Тихоокеанские Отдаленные Острова Соединенных Штатов', 'Мальдивы', 'Мальта', 'Марокко', 'Мартиника', 'Маршалловы Острова', 'Мексика', 'Микронезия', 'Мозамбик', 'Молдова', 'Монако', 'Монголия', 'Монтсеррат', 'Мьянма',
        'Намибия', 'Науру', 'Непал', 'Нигер', 'Нигерия', 'Нидерланды', 'Никарагуа', 'Ниуэ', 'Новая Зеландия', 'Новая Каледония', 'Норвегия',
        'Объединенные Арабские Эмираты', 'Оман', 'Острова Кайман', 'Острова Кука', 'Острова Теркс и Кайкос', 'Остров Буве', 'Остров Мэн', 'Остров Норфолк', 'Остров Рождества', 'Остров Херд и Острова Макдональд',
        'Пакистан', 'Палау', 'Палестина', 'Панама', 'Папуа-Новая Гвинея', 'Парагвай', 'Перу', 'Питкерн', 'Польша', 'Португалия', 'Пуэрто-Рико',
        'Республика Македония', 'Реюньон', 'Россия', 'Руанда', 'Румыния',
        'Самоа', 'Сан-Марино', 'Сан-Томе и Принсипи', 'Саудовская Аравия', 'Свазиленд', 'Святая Елена, Остров Вознесения, Тристан-да-кунья', 'Северные Марианские Острова', 'Сейшелы', 'Сен-Бартелеми', 'Сен-Мартен', 'Сенегал', 'Сент-Винсент и Гренадины', 'Сент-Китс и Невис', 'Сент-Люсия', 'Сент-Пьер и Микелон', 'Сербия', 'Сингапур', 'Сирийская Арабская Республика', 'Словакия', 'Словения', 'Соединенные Штаты Америки', 'Соломоновы Острова', 'Сомали', 'Судан', 'Суринам', 'Сьерра-Леоне',
        'Таджикистан', 'Таиланд', 'Тайвань', 'Танзания', 'Тимор-лесте', 'Того', 'Токелау', 'Тонга', 'Тринидад и Тобаго', 'Тувалу', 'Тунис', 'Туркмения', 'Турция',
        'Уганда', 'Узбекистан', 'Украина', 'Уоллис и Футуна', 'Уругвай',
        'Фарерские острова', 'Фиджи', 'Филиппины', 'Финляндия', 'Фолклендские острова', 'Франция', 'Французская Гвиана', 'Французская Полинезия', 'Французские Южные Территории',
        'Хорватия',
        'Центрально-Африканская Республика',
        'Чад', 'Черногория', 'Чехия', 'Чили',
        'Швейцария', 'Швеция', 'Шпицберген и Ян-Майен', 'Шри-Ланка',
        'Эквадор', 'Экваториальная Гвинея', 'Эландские Острова', 'Эль-Сальвадор', 'Эритрея', 'Эстония', 'Эфиопия',
        'Южная Африка', 'Южная Джорджия и Южные Сандвичевы Острова', 'Южная Осетия', 'Южный Судан',
        'Ямайка', 'Япония',
    ];

    protected static $region = [
        'Амурская', 'Архангельская', 'Астраханская', 'Белгородская', 'Брянская',
        'Владимирская', 'Волгоградская', 'Вологодская', 'Воронежская', 'Ивановская',
        'Иркутская', 'Калининградская', 'Калужская', 'Кемеровская', 'Кировская',
        'Костромская', 'Курганская', 'Курская', 'Ленинградская', 'Липецкая',
        'Магаданская', 'Московская', 'Мурманская', 'Нижегородская', 'Новгородская',
        'Новосибирская', 'Омская', 'Оренбургская', 'Орловская', 'Пензенская',
        'Псковская', 'Ростовская', 'Рязанская', 'Самарская', 'Саратовская',
        'Сахалинская', 'Свердловская', 'Смоленская', 'Тамбовская', 'Тверская',
        'Томская', 'Тульская', 'Тюменская', 'Ульяновская', 'Челябинская',
        'Читинская', 'Ярославская',
    ];

    protected static $city = [
        'Балашиха', 'Видное', 'Волоколамск', 'Воскресенск', 'Дмитров',
        'Домодедово', 'Дорохово', 'Егорьевск', 'Зарайск', 'Истра',
        'Кашира', 'Клин', 'Коломна', 'Красногорск', 'Лотошино',
        'Луховицы', 'Люберцы', 'Можайск', 'Москва', 'Мытищи',
        'Наро-Фоминск', 'Ногинск', 'Одинцово', 'Озёры', 'Орехово-Зуево',
        'Павловский Посад', 'Подольск', 'Пушкино', 'Раменское', 'Сергиев Посад',
        'Серебряные Пруды', 'Серпухов', 'Солнечногорск', 'Ступино', 'Талдом',
        'Чехов', 'Шатура', 'Шаховская', 'Щёлково',
    ];

    protected static $street = [
        'Косиора', 'Ладыгина', 'Ленина', 'Ломоносова', 'Домодедовская', 'Гоголя', '1905 года', 'Чехова', 'Сталина',
        'Космонавтов', 'Гагарина', 'Славы', 'Бухарестская', 'Будапештсткая', 'Балканская',
    ];

    protected static $addressFormats = [
        '{{postcode}}, {{region}} {{regionSuffix}}, {{cityPrefix}} {{city}}, {{streetPrefix}} {{street}}, {{buildingNumber}}',
    ];

    protected static $streetAddressFormats = [
        '{{streetPrefix}} {{street}}, {{buildingNumber}}',
    ];

    public static function buildingNumber()
    {
        return static::numerify(static::randomElement(static::$buildingNumber));
    }

    public function address()
    {
        $format = static::randomElement(static::$addressFormats);

        return $this->generator->parse($format);
    }

    public static function country()
    {
        return static::randomElement(static::$country);
    }

    public static function postcode()
    {
        return static::toUpper(static::bothify(static::randomElement(static::$postcode)));
    }

    public static function regionSuffix()
    {
        return static::randomElement(static::$regionSuffix);
    }

    public static function region()
    {
        return static::randomElement(static::$region);
    }

    public static function cityPrefix()
    {
        return static::randomElement(static::$cityPrefix);
    }

    public function city()
    {
        return static::randomElement(static::$city);
    }

    public static function streetPrefix()
    {
        return static::randomElement(static::$streetPrefix);
    }

    public static function street()
    {
        return static::randomElement(static::$street);
    }
}
