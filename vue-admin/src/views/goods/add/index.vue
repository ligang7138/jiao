<template>
  <div class="goods-add">
    <el-form ref="formRef" :model="formData" :rules="formRules" label-width="150px" class="goods-form">
      <!-- 商品信息 - 与旧系统完全一致 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">商品信息</span>
          </div>
        </template>

        <el-form-item label="商品名称" prop="goods_name">
          <el-input
            v-model="formData.goods_name"
            placeholder="商品名称"
            maxlength="30"
            show-word-limit
          />
        </el-form-item>

        <el-form-item label="详细描述" prop="remark">
          <el-input
            v-model="formData.remark"
            type="textarea"
            :rows="4"
            placeholder="详细描述"
          />
        </el-form-item>

        <el-form-item label="一级分类" prop="cate_id">
          <el-select
            v-model="formData.cate_id"
            placeholder="请选择"
            style="width: 50%"
            @change="handleCategoryChange"
          >
            <el-option v-for="item in categoryList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="二级分类" prop="scate_id">
          <el-select
            v-model="formData.scate_id"
            placeholder="请选择"
            style="width: 50%"
          >
            <el-option v-for="item in subCategoryList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="规 格" prop="spec">
          <el-input
            v-model="formData.spec"
            placeholder="规格"
            maxlength="30"
            show-word-limit
          />
        </el-form-item>

        <el-form-item label="单 位" prop="unit">
          <el-select
            v-model="formData.unit"
            placeholder="请选择"
            style="width: 50%"
          >
            <el-option v-for="item in unitList" :key="item.id" :label="item.name" :value="item.name" />
          </el-select>
        </el-form-item>

        <el-form-item label="商品属性" prop="attr">
          <el-select v-model="formData.attr" style="width: 50%">
            <el-option label="非标品" :value="1" />
            <el-option label="标品" :value="2" />
            <el-option label="特种品" :value="3" />
          </el-select>
        </el-form-item>

        <el-form-item label="等级" prop="level">
          <el-select v-model="formData.level" style="width: 50%">
            <el-option label="普通" :value="1" />
            <el-option label="精品" :value="2" />
          </el-select>
        </el-form-item>
      </el-card>

      <!-- 渠道设置 - 与旧系统完全一致 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">渠道设置</span>
          </div>
        </template>

        <el-form-item label="教师专用" prop="goods_type">
          <el-radio-group v-model="formData.goods_type">
            <el-radio :value="0">否</el-radio>
            <el-radio :value="1">是</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="议价商品" prop="goods_channel">
          <el-radio-group v-model="formData.goods_channel">
            <el-radio :value="0">否</el-radio>
            <el-radio :value="1">是</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-card>

      <!-- 图文详情 - 与旧系统完全一致 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">图文详情</span>
          </div>
        </template>

        <el-form-item label="商品图" prop="image_list">
          <div class="upload-section">
            <el-upload
              v-model:file-list="imageFileList"
              :action="uploadUrl"
              :headers="uploadHeaders"
              :limit="3"
              :on-success="handleImageSuccess"
              :on-remove="handleImageRemove"
              :on-preview="handlePreview"
              list-type="picture-card"
              accept="image/jpeg,image/png"
            >
              <el-icon><Plus /></el-icon>
              <template #tip>
                <div class="upload-tip">
                  请上传清晰的彩色照片，建议尺寸：宽600px 高600px，大小200K以内，最多3张，仅支持jpg、png格式
                </div>
              </template>
            </el-upload>
          </div>
        </el-form-item>

        <el-form-item label="详情图">
          <div class="upload-section">
            <el-upload
              v-model:file-list="detailImageFileList"
              :action="uploadUrl"
              :headers="uploadHeaders"
              :on-success="handleDetailImageSuccess"
              :on-remove="handleDetailImageRemove"
              :on-preview="handlePreview"
              list-type="picture-card"
              accept="image/jpeg,image/png"
            >
              <el-icon><Plus /></el-icon>
              <template #tip>
                <div class="upload-tip">
                  请上传清晰的彩色照片，建议尺寸：宽750px 高不限，大小200K以内，仅支持jpg、png格式
                </div>
              </template>
            </el-upload>
          </div>
        </el-form-item>
      </el-card>

      <!-- 其他信息 - 与旧系统完全一致 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">其他信息</span>
          </div>
        </template>

        <el-form-item label="品牌" prop="brand">
          <el-input
            v-model="formData.brand"
            placeholder="品牌"
            maxlength="50"
          />
        </el-form-item>

        <el-form-item label="产地" prop="place">
          <el-input
            v-model="formData.place"
            placeholder="产地"
            maxlength="100"
          />
        </el-form-item>

        <el-form-item label="保质期" prop="expire_date">
          <el-input
            v-model="formData.expire_date"
            placeholder="保质期"
            maxlength="50"
          />
        </el-form-item>
      </el-card>

      <!-- 底部操作按钮 -->
      <div class="form-footer">
        <el-button @click="handleCancel">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">保存</el-button>
      </div>
    </el-form>

    <!-- 图片预览 -->
    <el-dialog v-model="previewDialogVisible" title="图片预览" width="600px">
      <el-image :src="previewImageUrl" style="width: 100%" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { createGoods } from '@/api/modules/goods'
import { getCategoryList, getSubCategories } from '@/api/modules/category'
import { getToken } from '@/utils/auth'

const router = useRouter()

// 上传配置
const uploadUrl = import.meta.env.VITE_UPLOAD_URL || '/api/v1/upload/image'
const uploadHeaders = {
  Authorization: `Bearer ${getToken()}`
}

// 分类数据
const categoryList = ref([])
const subCategoryList = ref([])
const unitList = ref([])

// 图片文件列表
const imageFileList = ref([])
const detailImageFileList = ref([])

// 图片预览
const previewDialogVisible = ref(false)
const previewImageUrl = ref('')

// 提交loading
const submitLoading = ref(false)
const formRef = ref()

// 表单数据 - 与旧系统字段完全一致
const formData = reactive({
  goods_name: '',
  remark: '',
  cate_id: null,
  scate_id: null,
  spec: '',
  unit: '',
  attr: 1,
  level: 1,
  goods_type: 0,
  goods_channel: 0,
  image_list: [],
  detail_image_list: [],
  brand: '',
  place: '',
  expire_date: ''
})

// 表单验证规则 - 与旧系统验证逻辑一致
const formRules = {
  goods_name: [
    { required: true, message: '请输入商品名称', trigger: 'blur' },
    { max: 30, message: '商品名称最多30个字符', trigger: 'blur' }
  ],
  cate_id: [
    { required: true, message: '请选择一级分类', trigger: 'change' }
  ],
  scate_id: [
    { required: true, message: '请选择二级分类', trigger: 'change' }
  ],
  spec: [
    { required: true, message: '请输入规格', trigger: 'blur' },
    { max: 30, message: '规格最多30个字符', trigger: 'blur' }
  ],
  unit: [
    { required: true, message: '请选择单位', trigger: 'change' }
  ],
  attr: [
    { required: true, message: '请选择商品属性', trigger: 'change' }
  ],
  level: [
    { required: true, message: '请选择等级', trigger: 'change' }
  ],
  image_list: [
    {
      validator: (rule, value, callback) => {
        if (!value || value.length === 0) {
          callback(new Error('请上传商品图片'))
        } else {
          callback()
        }
      },
      trigger: 'change'
    }
  ]
}

// 获取一级分类列表
async function fetchCategoryList() {
  try {
    const { data } = await getCategoryList({ pid: 0, status: 1 })
    categoryList.value = data || []
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 获取商品单位列表
async function fetchUnitList() {
  try {
    // 从goods API获取单位列表
    const { data } = await fetch('/api/v1/goods/units').then(res => res.json())
    unitList.value = data || []
  } catch (error) {
    // 默认单位列表
    unitList.value = [
      { id: 1, name: '斤' },
      { id: 2, name: '公斤' },
      { id: 3, name: '个' },
      { id: 4, name: '袋' },
      { id: 5, name: '盒' },
      { id: 6, name: '瓶' },
      { id: 7, name: '包' }
    ]
  }
}

// 一级分类变化 - 与旧系统联动逻辑一致
async function handleCategoryChange(cateId) {
  formData.scate_id = null
  if (cateId) {
    try {
      const { data } = await getSubCategories(cateId)
      subCategoryList.value = data || []
    } catch (error) {
      subCategoryList.value = []
    }
  } else {
    subCategoryList.value = []
  }
}

// 商品图片上传成功
function handleImageSuccess(response, file, fileList) {
  if (response.code === 200) {
    formData.image_list.push(response.data.path)
  }
}

// 商品图片移除
function handleImageRemove(file, fileList) {
  const index = formData.image_list.findIndex(path => file.response?.data?.path === path)
  if (index > -1) {
    formData.image_list.splice(index, 1)
  }
}

// 详情图片上传成功
function handleDetailImageSuccess(response, file, fileList) {
  if (response.code === 200) {
    formData.detail_image_list.push(response.data.path)
  }
}

// 详情图片移除
function handleDetailImageRemove(file, fileList) {
  const index = formData.detail_image_list.findIndex(path => file.response?.data?.path === path)
  if (index > -1) {
    formData.detail_image_list.splice(index, 1)
  }
}

// 图片预览
function handlePreview(file) {
  previewImageUrl.value = file.url || file.response?.data?.url
  previewDialogVisible.value = true
}

// 取消
function handleCancel() {
  router.back()
}

// 提交表单 - 与旧系统提交逻辑一致
async function handleSubmit() {
  try {
    await formRef.value.validate()

    // 检查商品图片
    if (formData.image_list.length === 0) {
      ElMessage.error('请上传商品图片')
      return
    }

    submitLoading.value = true

    const submitData = {
      goods_name: formData.goods_name,
      remark: formData.remark,
      cate_id: formData.cate_id,
      scate_id: formData.scate_id,
      spec: formData.spec,
      unit: formData.unit,
      attr: formData.attr,
      level: formData.level,
      goods_type: formData.goods_type,
      goods_channel: formData.goods_channel,
      image_list: formData.image_list,
      detail_image_list: formData.detail_image_list,
      brand: formData.brand,
      place: formData.place,
      expire_date: formData.expire_date
    }

    await createGoods(submitData)
    ElMessage.success('新增成功')
    router.push('/goods/list')
  } catch (error) {
    console.error('提交失败:', error)
    ElMessage.error(error.message || '新增失败')
  } finally {
    submitLoading.value = false
  }
}

// 初始化
onMounted(() => {
  fetchCategoryList()
  fetchUnitList()
})
</script>

<style lang="scss" scoped>
.goods-add {
  padding: 20px;

  .goods-form {
    max-width: 900px;
    margin: 0 auto;

    .form-card {
      margin-bottom: 20px;

      .card-header {
        .section-title {
          font-weight: bold;
          font-size: 16px;
          color: #333;
        }
      }
    }

    .upload-section {
      .upload-tip {
        font-size: 12px;
        color: #999;
        line-height: 1.5;
        margin-top: 8px;
      }
    }

    .form-footer {
      position: fixed;
      bottom: 0;
      left: 200px;
      right: 0;
      padding: 15px 20px;
      background: #fff;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: center;
      gap: 20px;
      z-index: 100;
    }
  }
}
</style>