![image-20260807105129445](C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260807105129445.png)

参考上述图片的布局，进行BOM建立和BOM修改的开发

注意图片只是一个实现的模版样例，具体情况请根据项目的实际代码风格和具体表字段进行编写

功能主要涉及到4张表，分别为bom_headers_all（BOM头），bom_lines_all（BOM身），sf_item_no（料号表），bom_substitutes_all（替代料表）

### E-R图说明：

![image-20260808095658291](C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260808095658291.png)

### 修改要求和实现逻辑细节：

BOM建立功能界面需要改成树状展示，左侧展示BOM的树状结构，支持展开和折叠，当右键某个BOM的时候，会弹出具体操作：有“新建”/”引用“/”删除“/”编辑“，参考下图：

<img src="C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260808102259121.png" alt="image-20260808102259121" style="zoom:67%;" />

新建：弹出新建物料的窗口参考如下，填写完并保存后，会在物料表里和BOM相关表里添加数据，该BOM就直接引用新生成的BOM

<img src="C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260808102748046.png" alt="image-20260808102748046" style="zoom:80%;" />

引用：弹出引用已经创建好的BOM的窗口参考如下，保存后会在相关BOM表里添加数据以此建立关系

<img src="C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260808103246118.png" alt="image-20260808103246118" style="zoom:80%;" />

删除：级联删除，该BOM下的子BOM全部都要删掉，应该可以设置外键约束来实现

编辑：编辑对应字段值

[^注意]: 如果是该BOM的对应物料分类是最小规格的原料，则新建按钮和引用按钮置灰



------

点击左侧的BOM，右侧会展示该BOM的树状层级信息，参考界面如下：

![image-20260808111303819](C:\Users\asus\AppData\Roaming\Typora\typora-user-images\image-20260808111303819.png)



具体功能大概就是这样，功能直接在原有BOM建立文件进行修改，界面ui应贴合项目风格，你先大致实现一下，保证逻辑通畅，符合业务需求。
