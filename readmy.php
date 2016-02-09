<?php
/**
 * Created by PhpStorm.
 * User: Дрон
 * Date: 09.02.2016
 * Time: 21:56
 */

require_once("core/model.php");
/*
 * Руководство как пользоваться
 */


/*
 * Пример создания моделей
 */

/**
 * @property PasswordField pass
 * @property CharField name
 * @property TextField descriction
 * @property DateTimeField created
 */
class User extends Model{
    public function init()
    {
        $this->name = new CharField(array("max_length"=>100,'default'=>'User'));
        $this->pass = new PasswordField(array("max_length"=>100));
        $this->descriction = new TextField();
        $this->created = new DateTimeField();
    }
}

/**
 * @property ForeignKey book_id
 * @property TextField text
 * @property FloatField reut
 * @property DateTimeField created
 * @property ForeignKey user_id
 * @property CharField title
 */
class Comment extends Model{

    public function init()
    {
        $this->title = new CharField(array("max_length"=>100));
        $this->text = new TextField();
        $this->reut = new FloatField();
        $this->created = new DateTimeField();
        $this->user_id = new ForeignKey(array("TO"=>User)); # связь с таблицой юзеров
        $this->book_id = new ForeignKey(array("TO"=>Book));
    }
}

/**
 * @property CharField title
 * @property TextField text
 * @property TimeField time
 * @property DateTimeField created
 * @property ForeignKey user_id
 */
class Book extends Model{

    public function init()
    {
        $this->title = new CharField(array("max_length"=>100,"DEFAULT"=>"User"));
        $this->text = new TextField();
        $this->time = new TimeField();
        $this->created = new DateTimeField();
        $this->user_id = new ForeignKey(array("TO"=>User));
    }
}

/*
 * Cоздание моделей в базе данных метод create_model()
 */

$User = new User();
$User->create_model();
$Book = new Book();
$Book->create_model();
$Comment = new Comment();
$Comment->create_model();

/*
 * Сохранение объекта в базе
 */

$User->name->set("сохранение первое");
$User->pass->set_password("Test"); /* set_password() Шифрует пароль методом md5 */
$User->save();/* save() Сохранение объекта в базе */

/*
 * Можно так же заносить значения полям с помощью методов
 * Объект->имя_поля("значение");
 */

echo 'До обновления'.$User->name.'<br>';
/* Если ты работешь с объектом который уже сохранил то следующее сохранение будет обновлять объект*/
$User->name("сохранение второе");
$User->pass->set_password("09.02.2016");
$User->save();
echo 'После обновления'.$User->name.'<br>';
/*
 * Функция hash_password проверяет пароль методом md5
 * Возвращает истину если проверка прошла
 */
$User->pass->hash_password("09.02.2016");



$Book->time("book1");
$Book->text("text book");
$Book->user_id($User->id); /*  Связаное поле, подаем id*/
$Book->save();

/*
 * ПОлучение записи по id
 * После получения значений из базы данных, данные буду храниться в поле
 * query_set
 * Либо можно присвоить результат переменной
 */
$User->get(1);
/*
 * Эти 2 варианта получения данных одинаковы
 */
$User->query_set; /*  Данные отработки*/
$dats = $User->get(1); /*  Данные отработки */
echo "obj User Pass = ".$User->name.'<br>';
echo "obj User Pass = ".$User->pass.'<br>';
echo "obj dats Name = ".$dats->name.'<br>';
echo "obj dats Name = ".$dats->pass.'<br>';
echo '<br>';
/*
 * Можно получить данные если вызвать обхект как функцию
 */
/* Запрос на получение всех данных */

$User = new User();
$User->name("Andre");
$User->pass->set_password("09.02.2016:11");
$User->save();
$User->get_all();


echo '<br>';
echo 'Первый метод поления данных User() вызов объекта<br>';
echo '<br>';
/** @var User $field */
foreach($User() as $field){
    echo "pass=".$field->pass.'<br>';
    echo "name=".$field->name.'<br>';
    echo "created=".$field->created.'<br>';
    echo "descriction=".$field->descriction.'<br>';
    echo '<br>';
}

/*
*  Получение данных одинаково с первым методом
*/
echo '<br>';
echo 'Второй метод поления данных query_set<br>';
echo '<br>';
foreach($User->query_set as $field){
    echo "pass=".$field->pass.'<br>';
    echo "name=".$field->name.'<br>';
    echo "created=".$field->created.'<br>';
    echo "descriction=".$field->descriction.'<br>';
    echo '<br>';
}

$Book = new Book();
$Book->text("test2");
$Book->title("test2-title");
$Book->user_id($User->get(1)->id);
$Book->save();
echo "Книга сохранена<br>";
echo "<br><br>Book";
/** @var Book $field */
/*  Если передать значение то вернется количество записей сколько передали в значении*/
echo '<br>';
echo 'Второй метод поления данных Book(2) - получим 2 записи<br>';
echo '<br>';
$i = 1;
foreach($Book(2) as $field){
    echo "Запись $i<br>";
    echo "title=".$field->title.'<br>';
    echo "name=".$field->text.'<br>';
    echo "created=".$field->created.'<br>';
    echo "descriction=".$field->user_id.'<br>';
    echo '<br>';
    $i++;
}

$User = new User();
$User->pass->set_password('123123');
$User->save();
echo '<br>';


/*  Фильтрация по полям*/
# можно через замятую передать неограниченое количетсво параметров
$User->filter('id=1'); # $User->filter('id=1', 'name='Andre');
$User->name("Admin");
$User->save();
echo $User->name;
echo '<br>';