<?php

namespace Database\Seeders;

use App\DTO\CreateRequestData;
use App\DTO\DecisionData;
use App\Enums\RequestCategory;
use App\Enums\Role;
use App\Http\Actions\CancelRequestAction;
use App\Http\Actions\CreateRequestAction;
use App\Http\Actions\DecideRequestAction;
use App\Http\Actions\SubmitRequestAction;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    // гоняем данные через те же экшены что и в вебе, иначе легко получить сид
    // который не соответствует правилам
    public function run(): void
    {
        $people = $this->people();

        $create = app(CreateRequestAction::class);
        $submit = app(SubmitRequestAction::class);
        $decide = app(DecideRequestAction::class);
        $cancel = app(CancelRequestAction::class);

        foreach ($this->requests() as $row) {
            $author = $people[$row['author']];

            $request = $create->handle($author, new CreateRequestData(
                title: $row['title'],
                description: $row['description'],
                amount: $row['amount'],
                category: RequestCategory::from($row['category']),
            ));

            if ($row['status'] !== 'draft') {
                $request = $submit->handle($request, $author);
            }

            foreach ($row['decisions'] as $decision) {
                $request = $decide->handle(
                    $request,
                    $people[$decision['by']],
                    new DecisionData(
                        approve: $decision['approve'],
                        comment: $decision['comment'],
                        version: $request->version,
                    ),
                );
            }

            if ($row['status'] === 'cancelled') {
                $cancel->handle($request, $author);
            }

            $this->backdate($request, $row['days']);
        }
    }

    /**
     * @return array<string, User>
     */
    private function people(): array
    {
        $rows = [
            'maria' => ['Мария Соколова', 'maria@greenlight.dev', Role::Employee, 'Маркетинг'],
            'dmitry' => ['Дмитрий Орлов', 'dmitry@greenlight.dev', Role::Employee, 'IT'],
            'olga' => ['Ольга Ким', 'olga@greenlight.dev', Role::Employee, 'Операционный отдел'],
            'anna' => ['Анна Петрова', 'anna@greenlight.dev', Role::Manager, 'Операционный отдел'],
            'igor' => ['Игорь Лебедев', 'igor@greenlight.dev', Role::Cfo, 'Финансы'],
        ];

        $people = [];

        foreach ($rows as $key => [$name, $email, $role, $department]) {
            $people[$key] = User::create([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'role' => $role,
                'department' => $department,
            ]);
        }

        return $people;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function requests(): array
    {
        return [
            [
                'author' => 'dmitry',
                'title' => 'Замена ноутбуков в отделе продаж',
                'description' => 'Пять машин 2020 года не тянут CRM и браузер одновременно. Берём одинаковую конфигурацию, чтобы проще было обслуживать.',
                'amount' => 240000,
                'category' => 'it',
                'status' => 'approved',
                'days' => 18,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Согласовано, парк техники давно просил обновления.'],
                    ['by' => 'igor', 'approve' => true, 'comment' => 'В рамках квартального бюджета на IT.'],
                ],
            ],
            [
                'author' => 'olga',
                'title' => 'Канцелярия на квартал',
                'description' => 'Бумага, картриджи, папки. Заказываем у текущего поставщика, цены не менялись с весны.',
                'amount' => 18500,
                'category' => 'office',
                'status' => 'approved',
                'days' => 14,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Ок.'],
                ],
            ],
            [
                'author' => 'maria',
                'title' => 'Подписка на аналитику продукта',
                'description' => 'Нужен доступ к воронке и когортам для двух маркетологов. Сейчас всё собираем вручную в таблицах.',
                'amount' => 96000,
                'category' => 'services',
                'status' => 'pending',
                'days' => 1,
                'decisions' => [],
            ],
            [
                'author' => 'olga',
                'title' => 'Аренда коворкинга для команды',
                'description' => 'Месяц работы распределённой команды: 8 рабочих мест, переговорная, интернет.',
                'amount' => 120000,
                'category' => 'services',
                'status' => 'pending',
                'days' => 3,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Дешевле, чем открывать ещё один офис.'],
                ],
            ],
            [
                'author' => 'dmitry',
                'title' => 'Оборудование для переговорной',
                'description' => 'Камера, микрофон и колонка для созвонов с клиентами. Текущая камера пишет звук с эхом.',
                'amount' => 78000,
                'category' => 'it',
                'status' => 'pending',
                'days' => 2,
                'decisions' => [],
            ],
            [
                'author' => 'maria',
                'title' => 'Курс по продуктовой аналитике',
                'description' => 'Два места на шестинедельный курс. Внутри практика на реальных данных.',
                'amount' => 45000,
                'category' => 'hr',
                'status' => 'approved',
                'days' => 21,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Учимся на своих задачах, полезно.'],
                ],
            ],
            [
                'author' => 'maria',
                'title' => 'Фотограф для лендинга',
                'description' => 'Съёмка команды и офиса на полдня. Нужны горизонтальные кадры под новый дизайн.',
                'amount' => 32000,
                'category' => 'marketing',
                'status' => 'draft',
                'days' => 0,
                'decisions' => [],
            ],
            [
                'author' => 'olga',
                'title' => 'Релокация студии',
                'description' => 'Переезд в помещение рядом с метро: перевозка мебели, монтаж, подключение.',
                'amount' => 480000,
                'category' => 'services',
                'status' => 'rejected',
                'days' => 9,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'По срокам и смете вопросов нет.'],
                    ['by' => 'igor', 'approve' => false, 'comment' => 'Сейчас не готовы фиксировать такие расходы на переезд. Вернёмся в следующем квартале.'],
                ],
            ],
            [
                'author' => 'dmitry',
                'title' => 'Мониторы для дизайнеров',
                'description' => 'Два 27" монитора с калибровкой цвета, чтобы не спорить о нюансах оттенков на созвонах.',
                'amount' => 154000,
                'category' => 'it',
                'status' => 'approved',
                'days' => 30,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Давно в бэклоге.'],
                    ['by' => 'igor', 'approve' => true, 'comment' => 'Согласовано.'],
                ],
            ],
            [
                'author' => 'olga',
                'title' => 'Тимбилдинг в загородном отеле',
                'description' => 'Два дня на 28 человек: проживание, питание, активности.',
                'amount' => 60000,
                'category' => 'hr',
                'status' => 'cancelled',
                'days' => 7,
                'decisions' => [],
            ],
            [
                'author' => 'maria',
                'title' => 'Оплата CRM на год',
                'description' => 'Годовая лицензия на 12 мест. При оплате вперёд скидка 15%.',
                'amount' => 210000,
                'category' => 'services',
                'status' => 'approved',
                'days' => 25,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Переезжаем с прошлой системы.'],
                    ['by' => 'igor', 'approve' => true, 'comment' => 'Скидка перекрывает расходы на миграцию.'],
                ],
            ],
            [
                'author' => 'dmitry',
                'title' => 'Микрофоны для подкаста',
                'description' => 'Два динамических микрофона и стойки. Записываем интервью с клиентами раз в две недели.',
                'amount' => 27000,
                'category' => 'it',
                'status' => 'pending',
                'days' => 4,
                'decisions' => [],
            ],
            [
                'author' => 'maria',
                'title' => 'Печать брендированных блокнотов',
                'description' => 'Тираж 300 штук к конференции. Нужна твёрдая обложка и логотип на корешке.',
                'amount' => 12000,
                'category' => 'marketing',
                'status' => 'draft',
                'days' => 0,
                'decisions' => [],
            ],
            [
                'author' => 'olga',
                'title' => 'Обучение руководителей',
                'description' => 'Программа для четырёх тимлидов: обратная связь, планирование, разбор конфликтов.',
                'amount' => 130000,
                'category' => 'hr',
                'status' => 'pending',
                'days' => 5,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Состав группы согласовали с HR.'],
                ],
            ],
            [
                'author' => 'dmitry',
                'title' => 'Кофемашина в офис',
                'description' => 'Зерновая машина на 40 чашек в день, аренда с обслуживанием.',
                'amount' => 84000,
                'category' => 'office',
                'status' => 'rejected',
                'days' => 11,
                'decisions' => [
                    ['by' => 'anna', 'approve' => false, 'comment' => 'Возьмём в аренду без выкупа и обсудим в следующем квартале.'],
                ],
            ],
            [
                'author' => 'maria',
                'title' => 'Логистика для выставки',
                'description' => 'Стенд, доставка, монтаж и обратная перевозка. Участвуем впервые, бюджет считали с запасом.',
                'amount' => 350000,
                'category' => 'services',
                'status' => 'approved',
                'days' => 35,
                'decisions' => [
                    ['by' => 'anna', 'approve' => true, 'comment' => 'Смету проверили, всё похоже на правду.'],
                    ['by' => 'igor', 'approve' => true, 'comment' => 'Ок, но по итогам выставки нужен отчёт по лидам.'],
                ],
            ],
        ];
    }

    // если все даты одинаковые, видно что это сид. сдвигаем историю заявки назад
    private function backdate(PurchaseRequest $request, int $days): void
    {
        if ($days === 0) {
            return;
        }

        $target = now()->subDays($days)->subHours(3);
        $delta = $request->created_at->getTimestamp() - $target->getTimestamp();

        $shift = fn (?Carbon $date) => $date?->copy()->subSeconds($delta);

        DB::table('purchase_requests')->where('id', $request->id)->update([
            'created_at' => $shift($request->created_at),
            'updated_at' => $shift($request->updated_at),
            'submitted_at' => $shift($request->submitted_at),
            'decided_at' => $shift($request->decided_at),
        ]);

        foreach (DB::table('approvals')->where('purchase_request_id', $request->id)->get() as $approval) {
            DB::table('approvals')->where('id', $approval->id)->update([
                'created_at' => $shift(Carbon::parse($approval->created_at)),
                'updated_at' => $shift(Carbon::parse($approval->updated_at)),
                'decided_at' => $approval->decided_at ? $shift(Carbon::parse($approval->decided_at)) : null,
            ]);
        }

        foreach (DB::table('audit_events')->where('purchase_request_id', $request->id)->get() as $event) {
            DB::table('audit_events')->where('id', $event->id)->update([
                'created_at' => $shift(Carbon::parse($event->created_at)),
            ]);
        }
    }
}
