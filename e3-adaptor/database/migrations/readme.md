# 数据库迁移
一个迁移类包含两个方法： up 和 down。up 方法是用于新增数据库的数据表、字段或者索引的，而 down 方法应该与 up 方法的执行操作相反
如果你是在版本低于 5.7.7 的 MySQL，就需要手动配置数据库迁移的默认字符串长度。
即在 AppServiceProvider 中调用 Schema::defaultStringLength 方法来配置它。
```php
public function boot()
{
    Schema::defaultStringLength(191);
}
```
## 常用方法
1. 创建迁移文件

    `php artisan make:migration create_users_table`

2. 新建表
    ```php
    <?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class CreateFlightsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            // 创建
            Schema::create('flights', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('airline');
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::drop('flights');
        }
    }
    ```
3. 修改表
    ```php
     // 判断表是否存在
      if (Schema::hasTable('users')) {
          //
      }
     // 重命名表
     Schema::rename($from, $to);
     // 删除表
     Schema::dropIfExists('users');
    ```
3. 修改字段
    ```php
    Schema::table('users', function (Blueprint $table) {
       $table->string('name', 50)->change(); //变更字段
       $table->renameColumn('from', 'to'); // 重命名字段
       $table->dropColumn('votes'); // 删除字段
    });
    ```
5. 索引操作
    ```php
    $table->string('email')->unique();
    $table->unique('email');
    $table->index(['account_id', 'created_at']);
    $table->primary(['id', 'parent_id']);	
    $table->dropIndex('geo_state_index');	
    $table->dropPrimary('users_id_primary');	
    ```
6. 运行迁移

    `php artisan migrate`
7. 回滚迁移
    
    ```php
       php artisan migrate:rollback // 回滚上一次迁移
       php artisan migrate:rollback --step=5 // 回滚最后五个迁移
       php artisan migrate:reset // 回滚全部
       php artisan migrate:fresh // 删除数据中所有的数据表并在之后执行 migrate 命令
    ```
## Official Documentation

参考[官方文档](https://learnku.com/docs/laravel/6.x/migrations/5173)