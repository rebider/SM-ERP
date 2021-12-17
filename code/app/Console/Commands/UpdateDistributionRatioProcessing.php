<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/5/31
     * Time: 9:16
     */

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Log;

    class UpdateDistributionRatioProcessing extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'command:UpdateDistributionRatioProcessing';
        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = '定时更新配货比';

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle()
        {
            //执行代码
            $time = Date('Y-m-d H:i:s');
            $this->info("[{$time}]start");
            $obj = new \App\Console\Logic\UpdateDistributionRatio($this);
            $obj->run();
            $time = Date('Y-m-d H:i:s');
            $this->info("[{$time}]end");
        }
    }