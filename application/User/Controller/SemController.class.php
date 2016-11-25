<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/9
 * Time: 14:45
 */

namespace User\Controller;


use Common\Controller\HomebaseController;

class SemController extends HomebaseController
{
    private $hasSemSupport;
    private $sem;
    private $shm_key = 1;

    public function acquire() {
        if ( $this->hasSemSupport ) {
            return sem_acquire( $this->sem );
        }
        return true;
    }

    public function release() {
        if ( $this->hasSemSupport ) {
            return sem_release( $this->sem );
        }
        return true;
    }

    /**
     * 对顺序号发生器进行初始化。
     * 仅在服务器启动后的第一次调用有效，此后再调用此方法没有实际作用。
     * @param int $start 产生顺序号的起始值。
     * @return boolean 返回 true 表示成功。
     */
    public function init( $start = 1 )
    {
        // 通过信号量实现互斥，避免对共享内存的访问冲突
        if ( ! $this->acquire() ) {
            return false;
        }

        // 打开共享内存
        $shm_id = shmop_open( $this->shm_key, 'n', 0644, 4 );
        if ( empty($shm_id) ) {
            // 因使用了 'n' 模式，如果无法打开共享内存，可以认为该共享内存已经创建，无需再次初始化
            $this->release();
            return true;
        }

        // 在共享内存中写入初始值
        $size = shmop_write( $shm_id, pack( 'L', $start ), 0 );
        if ( $size != 4 ) {
            shmop_close( $shm_id );
            $this->release();
            return false;
        }

        // 关闭共享内存，释放信号量
        shmop_close( $shm_id );
        $this->release();
        return true;
    }

    /**
     * 产生下一个顺序号。
     * @return int 产生的顺序号
     */
    public function next()
    {
        // 通过信号量实现互斥，避免对共享内存的访问冲突
        if ( ! $this->acquire() ) {
            return 0;
        }

        // 打开共享内存
        $shm_id = shmop_open( $this->shm_key, 'w', 0, 0 );
        if ( empty($shm_id) ) {
            $this->release();
            return 0;
        }

        // 从共享内存中读出顺序号
        $data = shmop_read( $shm_id, 0, 4 );
        if ( empty($data) ) {
            $this->release();
            return 0;
        }

        $arr = unpack( 'L', $data );
        $seq = $arr[1];

        // 把下一个顺序号写入共享内存
        $size = shmop_write( $shm_id, pack( 'L', $seq + 1 ), 0 );
        if ( $size != 4 ) {
            $this->release();
            return 0;
        }

        // 关闭共享内存，释放信号量
        shmop_close( $shm_id );
        $this->release();
        return $seq;
    }
}