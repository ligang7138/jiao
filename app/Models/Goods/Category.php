<?php

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 商品分类模型
 * 对应表: category
 */
class Category extends Model
{
    use HasFactory;

    protected $table = 'category';

    protected static function newFactory()
    {
        return \Database\Factories\CategoryFactory::new();
    }

    protected $fillable = [
        'pid',
        'name',
        'logo',
        'float_rate_cap',
        'allow_report_after_send',
        'sort',
        'status',
    ];

    protected $casts = [
        'pid' => 'integer',
        'float_rate_cap' => 'decimal:4',
        'allow_report_after_send' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 父级分类
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'pid');
    }

    /**
     * 子分类
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'pid')->orderBy('sort');
    }

    /**
     * 分类下的商品（一级分类）
     */
    public function goods()
    {
        return $this->hasMany(Goods::class, 'cate_id');
    }

    /**
     * 递归获取所有子分类
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * 获取树形结构
     */
    public static function getTree(): array
    {
        $categories = self::with('children')
            ->where('pid', 0)
            ->orderBy('sort')
            ->get();

        return self::buildTree($categories);
    }

    /**
     * 构建树形数组
     */
    private static function buildTree($categories): array
    {
        $tree = [];
        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'name' => $category->name,
                'pid' => $category->pid,
                'logo' => $category->logo,
                'sort' => $category->sort,
                'children' => [],
            ];
            if ($category->children->count() > 0) {
                $node['children'] = self::buildTree($category->children);
            }
            $tree[] = $node;
        }
        return $tree;
    }
}