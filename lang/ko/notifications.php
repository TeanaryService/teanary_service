<?php

return [
    'order_created' => [
        'title' => '주문이 생성되었습니다',
        'message' => '주문 :order_no 이(가) 성공적으로 생성되었습니다. 주문 총액: :total',
    ],
    'order_paid' => [
        'title' => '주문이 결제되었습니다',
        'message' => '주문 :order_no 이(가) 성공적으로 결제되었습니다. 주문 총액: :total',
    ],
    'order_status_changed' => [
        'title' => '주문 상태가 변경되었습니다',
        'message' => '주문 :order_no 의 상태가 :old_status 에서 :new_status 로 변경되었습니다',
    ],
    'order_shipped' => [
        'title' => '주문이 배송되었습니다',
        'message' => '주문 :order_no 이(가) 배송되었습니다. 배송 방법: :shipping_method, 추적 번호: :tracking_number',
    ],
    'order_cancelled' => [
        'title' => '주문이 취소되었습니다',
        'message' => '주문 :order_no 이(가) 취소되었습니다. 사유: :reason',
    ],
    'my_notifications' => '내 알림',
    'no_notifications' => '알림 없음',
    'no_notifications_desc' => '아직 알림을 받지 않았습니다',
    'notification' => '알림',
    'mark_as_read' => '읽음으로 표시',
    'mark_all_as_read' => '모두 읽음으로 표시',
    'marked_as_read' => '읽음으로 표시됨',
    'all_marked_as_read' => '모두 읽음으로 표시됨',
    'deleted' => '알림이 삭제되었습니다',
    'confirm_delete' => '이 알림을 삭제하시겠습니까?',
];
