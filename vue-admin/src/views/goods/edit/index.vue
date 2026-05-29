<template>
  <div class="goods-edit">
    <el-form ref="formRef" :model="formData" :rules="formRules" label-width="150px" class="goods-form">
      <!-- 商品信息 - 与旧系统编辑页面字段一致 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">商品信息</span>
          </div>
        </template>

        <el-form-item label="商品编号">
          <el-input v-model="formData.goods_sn" disabled />
        </el-form-item>

        <el-form-item label="商品名称">
          <el-input v-model="formData.goods_name" disabled />
          <span class="form-tip">商品名称不可编辑，如需修改请新增商品</span>
        </el-form-item>

        <el-form-item label="详细描述" prop="remark">
          <el-input
            v-model="formData.remark"
            type="textarea"
            :rows="4"
            placeholder="详细描述"
          />
        </el-form-item>

        <el-form-item label="一级分类">
          <el-input v-model="formData.cate_name" disabled />
          <span class="form-tip">分类不可编辑</span>
        </el-form-item>

        <el-form-item label="二级分类">
          <el-input v-model="formData.scate_name" disabled />
        </el-form-item>

        <el-form-item label="规格">
          <el-input v-model="formData.spec" disabled />
        </el-form-item>

        <el-form-item label="单位">
          <el-input v-model="formData.unit" disabled />
        </el-form-item>

        <el-form-item label="商品属性">
          <el-input :value="getAttrText(formData.attr)" disabled />
        </el-form-item>

        <el-form-item label="等级">
          <el-input :value="formData.level === 1 ? '普通' : '精品'" disabled />
        </el-form-item>
      </el-card>

      <!-- 渠道设置 - 可编辑 -->
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

      <!-- 图文详情 - 可编辑 -->
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

      <!-- 其他信息 - 可编辑 -->
      <el-card class="form-card">
        <template #header>
          <div class="card-header">
            <span class="section-title">其他信息</span>
          </div>
        </template>

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
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getGoodsDetail, updateGoods } from '@/api/modules/goods'
import { getToken } from '@/utils/auth'

const router = useRouter()
const route = useRoute()

// 商品ID
const goodsId = computed(() => route.params.id)

// 上传配置
const uploadUrl = import.meta.env.VITE_UPLOAD_URL || '/api/v1/upload/image'
const uploadHeaders = {
  Authorization: `Bearer ${getToken()}`
}

// 图片文件列表
const imageFileList = ref([])
const detailImageFileList = ref([])

// 图片预览
const previewDialogVisible = ref(false)
const previewImageUrl = ref('')

// 提交loading
const submitLoading = ref(false)
const formRef = ref()

// 表单数据 - 与旧系统编辑页面字段一致
const formData = reactive({
  goods_sn: '',
  goods_name: '',
  remark: '',
  cate_id: null,
  cate_name: '',
  scate_id: null,
  scate_name: '',
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

// 表单验证规则
const formRules = {
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

// 商品属性文本
function getAttrText(attr) {
  const map = { 1: '非标品', 2: '标品', 3: '特种品' }
  return map[attr] || '-'
}

// 图片URL
const UPLOAD_URL = import.meta.env.VITE_UPLOAD_URL || '/upload/'

// 获取商品详情
async function fetchGoodsDetail() {
  try {
    const { data } = await getGoodsDetail(goodsId.value)

    // 填充表单数据
    Object.assign(formData, {
      goods_sn: data.goods_sn,
      goods_name: data.goods_name,
      remark: data.remark,
      cate_id: data.cate_id,
      cate_name: data.cate_name,
      scate_id: data.scate_id,
      scate_name: data.scate_name,
      spec: data.spec,
      unit: data.unit,
      attr: data.attr,
      level: data.level,
      goods_type: data.goods_type,
      goods_channel: data.goods_channel,
      image_list: data.image_list ? JSON.parse(data.image_list) : [],
      detail_image_list: data.detail_image_list ? JSON.parse(data.detail_image_list) : [],
      brand: data.brand,
      place: data.place,
      expire_date: data.expire_date
    })

    // 设置图片列表
    if (formData.image_list.length > 0) {
      imageFileList.value = formData.image_list.map((path, index) => ({
        name: `image-${index}`,
        url: UPLOAD_URL + path
      }))
    }

    if (formData.detail_image_list.length > 0) {
      detailImageFileList.value = formData.detail_image_list.map((path, index) => ({
        name: `detail-image-${index}`,
        url: UPLOAD_URL + path
      }))
    }
  } catch (error) {
    ElMessage.error('获取商品详情失败')
    router.back()
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
  const index = formData.image_list.findIndex(path => {
    const fileUrl = file.url || file.response?.data?.url
    return fileUrl && fileUrl.includes(path)
  })
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
  const index = formData.detail_image_list.findIndex(path => {
    const fileUrl = file.url || file.response?.data?.url
    return fileUrl && fileUrl.includes(path)
  })
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

    submitLoading.value = true

    const submitData = {
      remark: formData.remark,
      goods_type: formData.goods_type,
      goods_channel: formData.goods_channel,
      image_list: formData.image_list,
      detail_image_list: formData.detail_image_list,
      place: formData.place,
      expire_date: formData.expire_date
    }

    await updateGoods(goodsId.value, submitData)
    ElMessage.success('保存成功')
    router.push('/goods/list')
  } catch (error) {
    console.error('提交失败:', error)
    ElMessage.error(error.message || '保存失败')
  } finally {
    submitLoading.value = false
  }
}

// 初始化
onMounted(() => {
  if (goodsId.value) {
    fetchGoodsDetail()
  } else {
    ElMessage.error('商品ID不存在')
    router.back()
  }
})
</script>

<style lang="scss" scoped>
.goods-edit {
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

    .form-tip {
      font-size: 12px;
      color: #999;
      margin-left: 10px;
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