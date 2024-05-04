<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div class="shadow main-center-box-content">
        <div>
          <div style="height: 0.8rem"></div>
          <div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              竞赛名称
            </p>
            <div
              style="
                border-radius: 1.6rem;
                font-size: 1.66rem;
                color: var(--ltpp-title-color);
                font-weight: bold;
                text-align: center;
                overflow: hidden;
              "
            >
              <span
                :class="`${
                  contestdata.password ? 'el-icon-lock' : 'el-icon-trophy'
                }`"
              ></span>
              {{ contestdata.name }}
            </div>
          </div>

          <div style="height: 1rem"></div>
          <el-progress
            style="margin-left: 0.36rem; margin-right: 0.36rem"
            :text-inside="true"
            :percentage="
              end && begin && end - begin > 0
                ? nowtime >= begin
                  ? Math.min(100, ((nowtime - begin) / (end - begin)) * 100)
                  : 0
                : 0
            "
            status="success"
          ></el-progress>
          <div style="height: 1rem"></div>
          <p
            v-show="contestdata.content"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 1.16rem;
            "
          >
            竞赛简介
          </p>
          <div style="margin: 1rem 0rem"></div>

          <div class="markdown-body">
            <mavon-editor
              class="md"
              :toolbars="toolbars"
              :value="contestdata.content || '<br>'"
              :subfield="prop.subfield"
              :defaultOpen="prop.defaultOpen"
              :toolbarsFlag="prop.toolbarsFlag"
              :editable="prop.editable"
              :scrollStyle="prop.scrollStyle"
              :codeStyle="prop.codeStyle"
              :boxShadow="prop.boxShadow"
              :tabSize="prop.tabSize"
              :fontSize="prop.fontSize"
              :externalLink="externalLink"
              :toolbarsBackground="prop.toolbarsBackground"
              :previewBackground="prop.previewBackground"
              :editorBackground="prop.editorBackground"
              :xssOptions="xss_options"
              :stripIgnoreTagBody="stripIgnoreTagBody"
              style="
                color: var(--ltpp-box-text-color);
                min-height: 0rem;
                height: auto;
                border-width: 0rem;
              "
            >
            </mavon-editor>
          </div>
          <div
            v-show="
              isgetprolist &&
              ((isbegin && isjoin && !is_my_contest) || is_my_contest)
            "
          >
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              题目列表
            </p>
            <div>
              <div
                v-for="(tem, index) in problemList"
                :key="index"
                style="padding: 0.6rem 0rem 0.6rem 2rem; will-change: transform"
              >
                <el-tooltip
                  v-show="tem.hasac === 0"
                  class="item"
                  effect="dark"
                  :content="'未通过 P' + (index + 1) + '：' + tem.problemName"
                  placement="right"
                >
                  <el-tag
                    class="pulse-enter-active"
                    effect="dark"
                    v-show="tem.hasac === 0"
                    @click="toonepro(tem.id)"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      cursor: pointer;
                    "
                    >P{{ index + 1 + "：" + tem.problemName }}</el-tag
                  >
                </el-tooltip>

                <el-tooltip
                  v-show="tem.hasac === 1"
                  class="item"
                  effect="dark"
                  :content="'已通过 P' + (index + 1) + '：' + tem.problemName"
                  placement="right"
                >
                  <el-tag
                    class="pulse-enter-active"
                    effect="dark"
                    v-show="tem.hasac === 1"
                    type="success"
                    @click="toonepro(tem.id)"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      cursor: pointer;
                    "
                    >P{{ index + 1 + "：" + tem.problemName }}</el-tag
                  >
                </el-tooltip>
              </div>
            </div>
          </div>
          <!-- echarts -->
          <div v-show="echarts_finish">
            <div
              v-if="
                isbegin &&
                userdata &&
                echartsnum &&
                userdata.length > 0 &&
                contestdata.allpeople > 0 &&
                contestdata.allpeople <= 40 &&
                type != 'OI'
              "
            >
              <p
                style="
                  font-size: 1.06rem;
                  text-align: left;
                  font-weight: bold;
                  margin: 1rem 1rem 0.5rem 1.16rem;
                "
              >
                可视化排名
              </p>
              <div style="padding: 0.6rem 0rem 0.6rem 0rem">
                <div
                  id="rankchart"
                  :style="`
                    margin-left: 0.36rem;
                    margin-right: 0.36rem;
                    width: calc(${$store.state.max_width}px - 0.72rem);
                    height: 30rem;
                    background-color: rgba(var(--ltpp-main-bk-color), 0);
                    will-change: transform;
                  `"
                ></div>
              </div>
            </div>
          </div>
          <div style="height: 1.6rem"></div>
          <div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              创建者:
              <el-tag
                class="pulse-enter-active"
                @click="touserpage(contestdata.createrid)"
                effect="dark"
                style="font-size: 1.06rem; cursor: pointer"
                size="medium"
              >
                {{ contestdata.creater }}
              </el-tag>
            </p>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              赛制类型:
              <el-tag
                effect="dark"
                type="danger"
                style="font-size: 1.06rem"
                size="medium"
              >
                {{ contestdata.type }}
              </el-tag>
            </p>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              竞赛开始时间：
              <el-tag
                effect="dark"
                type="success"
                style="font-size: 1.06rem"
                size="medium"
              >
                {{ contestdata.begin }}
              </el-tag>
            </p>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              竞赛结束时间:
              <el-tag
                effect="dark"
                type="success"
                style="font-size: 1.06rem"
                size="medium"
              >
                {{ contestdata.end }}
              </el-tag>
            </p>
          </div>
          <div
            v-if="
              myrank &&
              resuserrank.length > 0 &&
              isbegin &&
              isshow &&
              isjoin &&
              (isacm ||
                is_my_contest ||
                issqs ||
                isioi ||
                (isoi && show_oi_rank))
            "
            style="margin: 1rem 0.66rem"
          >
            <el-alert
              title="我的排名："
              type="success"
              effect="dark"
              :description="`${myrank} / ${contestdata.allpeople}`"
              show-icon
              :closable="false"
            >
            </el-alert>
          </div>
          <div
            v-show="
              resuserrank.length > 0 &&
              isbegin &&
              isshow &&
              (isacm ||
                $store.state.admin ||
                issqs ||
                isioi ||
                (isoi && show_oi_rank))
            "
          >
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              竞赛排名
            </p>
          </div>
          <el-table
            v-show="
              resuserrank.length > 0 &&
              isbegin &&
              isshow &&
              (isacm ||
                $store.state.admin ||
                issqs ||
                isioi ||
                (isoi && show_oi_rank))
            "
            id="oIncomTable"
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'text-align': 'center',
              'font-size': '1.06rem',
              'overflow-x': 'hidden',
            }"
            :data="resuserrank"
            style="width: 100%"
            :row-class-name="tableRowClassName"
          >
            <el-table-column label="排名" width="100" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'排名：' + scope.row.index + '/' + total"
                  placement="right"
                >
                  <span
                    style="font-weight: bold; font-size: 1.06rem"
                    class="my-span"
                  >
                    {{ scope.row.index }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="用户" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'用户名：' + scope.row.name"
                  placement="right"
                >
                  <span
                    class="my-span"
                    @click="touserpage(scope.row.id)"
                    style="
                      cursor: pointer;
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: red;
                      overflow: hidden;
                    "
                  >
                    {{ scope.row.name.substr(0, 10) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column
              label="总AC数"
              width="100"
              v-if="isacm || issqs"
              align="center"
            >
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'总AC数目：' + scope.row.acnum"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="font-weight: bold; font-size: 1.06rem; color: red"
                  >
                    {{ scope.row.acnum }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column
              label="总分"
              width="100"
              v-if="isoi || isioi"
              align="center"
            >
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'总分数：' + scope.row.allscore"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="font-weight: bold; font-size: 1.06rem; color: red"
                  >
                    {{ scope.row.allscore }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="总用时" width="200" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="
                    '总用时：' +
                    Math.floor(scope.row.totaltime / 3600) +
                    '小时' +
                    Math.floor(
                      Math.floor(
                        scope.row.totaltime -
                          Math.floor(scope.row.totaltime / 3600) * 3600
                      ) / 60
                    ) +
                    '分钟' +
                    Math.floor(scope.row.totaltime % 60) +
                    '秒'
                  "
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;

                      color: deepskyblue;
                    "
                  >
                    {{
                      Math.floor(scope.row.totaltime / 3600) +
                      ":" +
                      Math.floor(
                        Math.floor(
                          scope.row.totaltime -
                            Math.floor(scope.row.totaltime / 3600) * 3600
                        ) / 60
                      ) +
                      ":" +
                      Math.floor(scope.row.totaltime % 60)
                    }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column
              width="120"
              v-for="(tempro, index) in problemIndex"
              :key="index"
              align="center"
            >
              <template slot="header">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'P' + (index + 1) + '：' + tempro.problemname"
                  placement="top"
                >
                  <span
                    class="my-span"
                    @click="toonepro(tempro.problemid)"
                    style="color: deeppink; cursor: pointer"
                    >{{ "P" + (index + 1) }}</span
                  >
                </el-tooltip>
              </template>
              <template slot-scope="scope">
                <span
                  class="my-span"
                  v-show="isacm || issqs"
                  @dblclick="lookcode(scope.row.id, tempro.problemid)"
                  style="font-weight: bold; font-size: 1rem; cursor: pointer"
                >
                  <span
                    class="my-span"
                    style="color: chartreuse"
                    v-show="scope.row.res[index].firstAcTime != -1"
                  >
                    {{
                      scope.row.res[index].waNum == 0
                        ? "✔"
                        : "-" + scope.row.res[index].waNum
                    }}
                  </span>
                  <span
                    class="my-span"
                    style="color: deeppink"
                    v-show="
                      scope.row.res[index].firstAcTime == -1 &&
                      scope.row.res[index].waNum != 0
                    "
                  >
                    {{ "-" + scope.row.res[index].waNum }}
                  </span>

                  <br v-show="scope.row.res[index].firstAcTime != -1" />
                  <span
                    class="my-span"
                    v-show="scope.row.res[index].firstAcTime != -1"
                    @dblclick="lookcode(scope.row.id, tempro.problemid)"
                    style="font-size: 1rem; color: var(--ltpp-box-text-color)"
                  >
                    {{
                      scope.row.res[index].firstAcTime != -1
                        ? `(${scope.row.res[index].firstAcTime})`
                        : ``
                    }}
                  </span>
                </span>

                <span
                  class="my-span"
                  v-show="isoi || isioi"
                  @dblclick="lookcode(scope.row.id, tempro.problemid)"
                  style="font-weight: bold; font-size: 1rem; cursor: pointer"
                >
                  <span
                    class="my-span"
                    style="color: chartreuse"
                    v-show="
                      scope.row.res[index].firstAcTime != -1 &&
                      scope.row.res[index].score == 100
                    "
                  >
                    {{ scope.row.res[index].score }}
                  </span>
                  <span
                    class="my-span"
                    style="color: deeppink"
                    v-show="
                      scope.row.res[index].firstAcTime != -1 &&
                      scope.row.res[index].score != 100
                    "
                  >
                    {{ scope.row.res[index].score }}
                  </span>
                  <br v-show="scope.row.res[index].firstAcTime != -1" />
                  <span
                    class="my-span"
                    v-show="scope.row.res[index].firstAcTime != -1"
                    @dblclick="lookcode(scope.row.id, tempro.problemid)"
                    style="font-size: 1rem; color: var(--ltpp-box-text-color)"
                  >
                    {{
                      scope.row.res[index].firstAcTime != -1
                        ? `(${scope.row.res[index].firstAcTime})`
                        : ``
                    }}
                  </span>
                </span>
              </template>
            </el-table-column>
          </el-table>

          <div
            v-show="
              resuserrank.length > 0 &&
              isbegin &&
              isshow &&
              (isacm ||
                $store.state.admin ||
                issqs ||
                isioi ||
                (isoi && show_oi_rank))
            "
            style="height: 2rem"
          ></div>
          <el-pagination
            v-show="
              resuserrank.length > 0 &&
              isbegin &&
              isshow &&
              (isacm ||
                $store.state.admin ||
                issqs ||
                isioi ||
                (isoi && show_oi_rank))
            "
            background
            style="text-align: center"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="page"
            :page-sizes="[20, 50, 100, 200]"
            :page-size="limit"
            layout="total, sizes, prev, pager, next, jumper"
            :total="total"
          ></el-pagination>
          <div style="height: 2rem"></div>

          <div style="margin-left: 1.6rem; margin-right: 1.6rem">
            <div>
              <span
                style="font-size: 1.06rem; color: #f56c6c; cursor: auto"
                class="el-icon-user-solid"
              >
                报名人数：{{ contestdata.allpeople }}</span
              >
              <div style="height: 1rem"></div>
              <span
                v-show="!isend && can_show_time"
                style="
                  margin-left: 4rem;
                  font-size: 1.06rem;
                  color: chartreuse;
                  cursor: auto;
                "
              >
                <div
                  v-show="
                    !isbegin && !isNaN(parseFloat(begin)) && isFinite(begin)
                  "
                >
                  距离竞赛开始还有：{{
                    Math.floor((begin - nowtime) / 1000 / 3600) +
                    " 小时 " +
                    Math.floor(
                      (Math.floor((begin - nowtime) / 1000) -
                        Math.floor((begin - nowtime) / 1000 / 3600) * 3600) /
                        60
                    ) +
                    " 分钟 " +
                    (Math.floor((begin - nowtime) / 1000) % 60) +
                    " 秒 "
                  }}
                </div>
                <div
                  v-show="
                    isbegin &&
                    !isend &&
                    !isNaN(parseFloat(end)) &&
                    isFinite(end)
                  "
                >
                  距离竞赛结束还有：{{
                    Math.floor((end - nowtime) / 1000 / 3600).toFixed(0) +
                    " 小时 " +
                    Math.floor(
                      (Math.floor(end - nowtime) / 1000 -
                        Math.floor(
                          Math.floor((end - nowtime) / 1000 / 3600) * 3600
                        )) /
                        60
                    ) +
                    " 分钟 " +
                    (Math.floor((end - nowtime) / 1000) % 60) +
                    " 秒 "
                  }}
                </div>
              </span>
            </div>
            <div style="height: 3.6rem"></div>
            <!-- 当展示排名就不展示按钮 -->
            <div style="display: flex; justify-content: space-around">
              <el-button
                round
                style=""
                v-if="
                  ($store.state.root && $store.state.my_name === 'root') ||
                  is_my_contest
                "
                @click="delrank()"
                width="auto"
                class="el-icon-delete-solid pulse-enter-active shadow"
              >
                清理缓存</el-button
              >
              <el-button
                v-if="$store.state.root || is_my_contest"
                round
                @click="lookPublicContestRank()"
                width="auto"
                class="el-icon-s-data pulse-enter-active shadow"
              >
                外链排名</el-button
              >
              <el-button
                v-if="
                  (($store.state.root && $store.state.my_name === 'root') ||
                    is_my_contest) &&
                  show_code_check_similarity
                "
                round
                @click="codeCheckSimilarity()"
                width="auto"
                class="el-icon-s-help pulse-enter-active shadow"
              >
                代码查重</el-button
              >
              <el-button
                v-if="
                  isoi &&
                  isbegin &&
                  !isshow &&
                  (isend || is_my_contest) &&
                  !show_oi_rank
                "
                round
                @click="lookoirank()"
                width="auto"
                class="el-icon-s-data pulse-enter-active shadow"
              >
                封榜排名</el-button
              >
              <el-button
                v-if="
                  isbegin &&
                  contestdata.allpeople > 0 &&
                  contestdata.allpeople <= 40 &&
                  type != 'OI'
                "
                round
                @click="download()"
                width="auto"
                class="el-icon-picture pulse-enter-active shadow"
              >
                下载图片</el-button
              >
              <el-button
                v-if="
                  ($store.state.root && $store.state.my_name === 'root') ||
                  is_my_contest
                "
                round
                @click="getProblemMD()"
                width="auto"
                class="el-icon-s-order pulse-enter-active shadow"
              >
                下载题目</el-button
              >
              <el-button
                v-if="
                  ($store.state.root && $store.state.my_name === 'root') ||
                  is_my_contest
                "
                round
                @click="getProblemSolveMD()"
                width="auto"
                class="el-icon-s-help pulse-enter-active shadow"
              >
                下载题解</el-button
              >
              <el-button
                v-if="!isjoin && canclick"
                round
                @click="middlewareJoinContest()"
                width="auto"
                class="el-icon-user-solid pulse-enter-active shadow"
              >
                报名竞赛</el-button
              >
              <el-button
                @click="
                  isSeeComment = false;
                  toback();
                "
                width="auto"
                round
                class="el-icon-s-unfold pulse-enter-active shadow"
              >
                【返回】</el-button
              >
            </div>
          </div>
          <div style="height: 3.6rem"></div>
        </div>
      </div>
      <el-dialog
        @contextmenu.prevent.native="isseecode = false"
        :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        :append-to-body="true"
        title="代码详情"
        :visible.sync="isseecode"
      >
        <ShowCode
          v-if="isseecode"
          :code="code"
          :language="codelanguage"
        ></ShowCode>
      </el-dialog>

      <el-dialog
        @contextmenu.prevent.native="isseepassword = false"
        :close-on-click-modal="false"
        width="30%"
        :append-to-body="true"
        title="参赛密码"
        :visible.sync="isseepassword"
      >
        <el-input
          placeholder="请输入参赛密码"
          style="font-size: 1.06rem"
          v-model.lazy="password"
          @keyup.enter.native="joincontest()"
        >
          <el-button slot="append" icon="el-icon-success" @click="joincontest()"
            >确定</el-button
          >
        </el-input>
      </el-dialog>
      <div style="height: 2rem"></div>
    </div>
  </div>
</template>

<script>
import echarts from "../../../public/static/echarts.min.js";
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
import ShowCode from "../../components/showcode.vue";
import "../../../public/md/markdown/github-markdown.min.css";

export default {
  name: "onecontest",
  components: {
    ShowCode,
  },
  async activated() {
    this.echarts_finish = false;
    this.rank_lock = false;
    this.isseetip = true;
    this.isgetprolist = false;
    this.echartsnum = 0;
    this.myrank = 0;
    this.show_code_check_similarity = true;
    this.showone_join_msg = false;
    this.isseepassword = false;
    this.password = "";
    if (
      !(
        this.$route &&
        this.$route.query &&
        this.$route.query.path &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.$router.go(-1);
      return;
    }
    this.limit = 20;
    this.page = 1;
    this.isseetip = true;
    this.contestid = urlencode.decode(this.$route.query.path, "gbk");
    this.isseecode = false;
    this.isacm = false;
    this.isoi = false;
    this.isioi = false;
    this.issqs = false;
    this.isshow = true;
    this.getdata();
    this.timer = setInterval(() => {
      this.gettime();
    }, 1000);
    this.$nextTick(() => {
      this.totop();
    });
    this.judgeisjoin();
    this.getproblemlist();
    this.frontendJudgeIsMyContest();
    this.updateEcharts();

    this.totaltime = 0;
    this.totaltime++;
    //当前时间戳
    this.nowtime = Date.now();
    //开始时间戳
    this.begin = new Date(this.contestdata.begin);
    this.begin = this.begin.getTime();
    //结束时间戳
    this.end = new Date(this.contestdata.end);
    this.end = this.end.getTime();

    if (this.begin <= this.nowtime && this.end >= this.nowtime) {
      this.isbegin = true;
      this.isend = false;
      this.updateEcharts();
    } else if (this.end < this.nowtime) {
      this.isbegin = true;
      this.isend = true;
      this.updateEcharts();
    }
    if (!this.isbegin || (this.isbegin && !this.isend)) {
      this.can_show_time = true;
    }
    if (this.isacm || this.issqs) {
      this.isshow = true;
      this.lookacmrank();
    } else if (this.isioi || this.isoi) {
      this.lookoirank();
    }
  },
  destroyed() {
    this.rank_lock = false;
    this.isseetip = false;
    clearInterval(this.timer);
    this.timer = null;
  },
  deactivated() {
    this.rank_lock = false;
    this.can_show_time = false;
    this.problemList = [];
    this.problemIndex = [];
    this.userdata = [];
    this.resrank = [];
    this.resuserrank = [];
    this.isseetip = false;
    clearInterval(this.timer);
    this.timer = null;
  },
  data() {
    return {
      password: "",
      isseepassword: false,
      echarts_finish: false,
      rank_lock: false,
      show_oi_rank: false,
      can_show_time: false,
      showone_join_msg: false,
      show_code_check_similarity: true,
      timer: null,
      myrank: 0,
      is_my_contest: false,
      echartsnum: 0,
      isgetprolist: false,
      isHidePagenum: true,
      total: 0,
      limit: 20,
      page: 1,
      isseetip: true,
      canclick: true,
      isseecode: false,
      codelanguage: "cpp",
      code: "",
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      type: "",
      isacm: false,
      isoi: false,
      isioi: false,
      issqs: false,
      isjoin: false,
      isshow: true,
      problemList: [],
      problemIndex: [],
      contestid: "",
      contestdata: {
        id: 0,
        name: "加载中",
        content: "加载中",
        begin: "加载中",
        end: "加载中",
        creater: "加载中",
        allpeople: "加载中",
        type: "加载中",
        createrid: 0,
      },
      /* context:  '',//输入的数据 */
      toolbars: {
        bold: true, // 粗体
        italic: true, // 斜体
        header: true, // 标题
        underline: true, // 下划线
        mark: true, // 标记
        superscript: true, // 上角标
        quote: true, // 引用
        ol: true, // 有序列表
        link: true, // 链接
        imagelink: false, // 图片链接

        code: true, // code
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: true, // 沉浸式阅读
        /* 1.3.5 */
        undo: true, // 上一步
        trash: false, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
      timedata: [],
      userdata: [],
      resrank: [],
      resuserrank: [],
      nowtime: 0,
      begin: 0,
      end: 0,
      isbegin: false,
      isend: false,
      totaltime: 0,
    };
  },
  methods: {
    async lookPublicContestRank() {
      let cache = window.sessionStorage.getItem("linuxurl");
      if (!cache) {
        cache = await this.getBackurl();
      }
      let url =
        cache + "/Contest/publicContestRank?contest_id=" + this.contestdata.id;
      this.copy(url);
      window.open(url);
    },
    handleCurrentChange(val) {
      this.page = val;
      if (
        this.contestdata.allpeople &&
        (this.contestdata.allpeople <= 40 || this.contestdata.allpeople > 0)
      ) {
        this.getrank();
      }
      this.rank_lock = false;
      if (this.isacm == true || this.issqs) {
        this.lookacmrank();
      } else if (this.isioi || this.isoi) {
        this.lookoirank();
      }
    },
    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (
        this.contestdata.allpeople &&
        (this.contestdata.allpeople <= 40 || this.contestdata.allpeople > 0)
      ) {
        this.getrank();
      }
      this.rank_lock = false;
      if (this.isacm == true || this.issqs) {
        this.lookacmrank();
      } else if (this.isioi || this.isoi) {
        this.lookoirank();
      }
    },
    tableRowClassName({ row }) {
      if (row.status === "正常运行" || row.status === "AC") {
        return "success-row";
      } else return "warning-row";
    },
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        color: "chartreuse",
        height: "50px",
        padding: "0rem",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      if (row.index < 4) {
        styleRes.color = "red";
        return styleRes;
      } else {
        styleRes.color = "chartreuse";
        return styleRes;
      }
    },

    changecodelanguage(language) {
      if (language == "C") {
        this.codelanguage = "c";
      } else if (language == "C++") {
        this.codelanguage = "cpp";
      } else if (language == "Go") {
        this.codelanguage = "go";
      } else if (language == "Java") {
        this.codelanguage = "java";
      } else if (language == "PHP") {
        this.codelanguage = "php";
      } else if (language == "JavaScript") {
        this.codelanguage = "javascript";
      } else if (language == "Python3") {
        this.codelanguage = "python";
      } else if (language == "Rust") {
        this.codelanguage = "rust";
      } else if (language == "C#") {
        this.codelanguage = "csharp";
      } else if (language == "TypeScript") {
        this.codelanguage = "typescript";
      } else if (language == "Ruby") {
        this.codelanguage = "ruby";
      } else {
        this.codelanguage = "cpp";
      }
    },

    async lookcode(userid, problemid) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/lookCode",
        portType: {
          process: "8795",
        },
        data: {
          user_id: userid,
          problem_id: problemid,
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      this.changecodelanguage(res.language);
      this.code = res?.data;
      this.isseecode = true;
    },
    async frontendJudgeIsMyContest() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/frontendJudgeIsMyContest",
        portType: {
          process: "8795",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.is_my_contest = res?.data == 1;
      }
    },
    async getProblemMD() {
      this.downloadUrlContent(
        "/Contest/getProblemMD",
        { contest_id: this.contestid },
        this.contestdata.name + "（赛题）.md"
      );
    },
    async getProblemSolveMD() {
      this.downloadUrlContent(
        "/Contest/getProblemSolveMD",
        { contest_id: this.contestid },
        this.contestdata.name + "（题解）.md"
      );
    },
    touserpage(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    async codeCheckSimilarity() {
      this.show_code_check_similarity = false;
      this.$msg({
        type: "success",
        message: "开始查重，请耐心等待！",
        duration: 3600,
        offset: 80,
      });
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/codeCheckSimilarity",
        portType: {
          process: "8797",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.show_code_check_similarity = true;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.show_code_check_similarity = true;
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async delrank() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/deleteRank",
        portType: {
          process: "8797",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.totaltime = 1;
        if (this.begin <= this.nowtime && this.end >= this.nowtime) {
          this.isbegin = true;
          this.isend = false;
          this.getproblemlist();
        } else if (this.end < this.nowtime) {
          this.isbegin = true;
          this.isend = true;
        }
        if (this.isacm == true || this.issqs === true) {
          await this.lookacmrank();
        } else if (this.isioi === true || this.isoi === true) {
          await this.lookoirank();
        }
        this.updateEcharts();
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 800,
          offset: 80,
        });
      }
    },
    async lookacmrank() {
      try {
        if (this.rank_lock) {
          return;
        }
        this.rank_lock = true;
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Contest/lookAcmExcelRank",
          portType: {
            process: "8789",
          },
          data: {
            contest_id: this.contestid,
            page: this.page,
            limit: this.limit,
          },
        }).catch((t) => {
          this.rank_lock = false;
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
          return;
        });
        if (res?.code == 1) {
          /* ACM赛制，SQS赛制 */
          this.isshow = true;
          this.resuserrank = res?.data;
          this.problemIndex = res.problemIndex;
          this.myrank = res.myrank;
          this.total = res.total;
        } else {
          this.isshow = false;
          !this.showone_join_msg &&
            (this.showone_join_msg = true) &&
            this.$msg({
              type: "error",
              message: res?.msg,
              duration: 800,
              offset: 80,
            });
        }
        this.rank_lock = false;
      } catch (err) {
        this.rank_lock = false;
      }
    },
    async lookoirank() {
      try {
        if (!this.isend && this.isoi && !this.is_my_contest) {
          return;
        }
        if (this.rank_lock) {
          return;
        }
        this.rank_lock = true;
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Contest/lookOiExcelRank",
          portType: {
            process: "8789",
          },
          data: {
            contest_id: this.contestid,
            page: this.page,
            limit: this.limit,
          },
        }).catch((t) => {
          this.rank_lock = false;
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
          return;
        });
        if (res?.code == 1) {
          /* OI赛制，IOI */
          this.show_oi_rank = true;
          this.resuserrank = res?.data;
          this.problemIndex = res.problemIndex;
          this.myrank = res.myrank;
          this.total = res.total;
          this.isshow = true;
        } else {
          this.show_oi_rank = false;
          this.isshow = false;
          !this.showone_join_msg &&
            (this.showone_join_msg = true) &&
            this.$msg({
              type: "error",
              message: res?.msg,
              duration: 800,
              offset: 80,
            });
        }
        this.rank_lock = false;
      } catch (err) {
        this.rank_lock = false;
      }
    },
    async judgeisjoin() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/judgeIsJoin",
        portType: {
          process: "8796",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.isjoin = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        this.isjoin = true;
      } else {
        this.isjoin = false;
      }
    },
    download() {
      let canvas = document.getElementsByTagName("canvas");
      if (canvas && canvas.length > 0) {
        // 创建标签
        let oA = document.createElement("a");
        // 设置下载名称
        oA.download = this.contestdata.name + ".png";
        // 设置地址以及文件类型
        oA.href = canvas[0].toDataURL("image/png");
        document.body.appendChild(oA);
        // 触发下载事件
        oA.click();
        // 移除元素
        oA.remove();
      }
    },
    middlewareJoinContest() {
      if (this.contestdata?.password) {
        this.isseepassword = true;
      } else {
        this.joincontest();
      }
    },
    async gettime() {
      this.totaltime++;
      if (this.totaltime >= 3600) {
        this.totaltime = 0;
      }
      //当前时间戳
      this.nowtime = Date.now();
      //开始时间戳
      this.begin = new Date(this.contestdata.begin);
      this.begin = this.begin.getTime();
      //结束时间戳
      this.end = new Date(this.contestdata.end);
      this.end = this.end.getTime();
      let t = (this.nowtime - this.begin) / 1000;
      // 比赛开始拉取题目
      if (
        t >= 0 &&
        this.isjoin &&
        !this.isgetprolist &&
        this.nowtime <= this.end
      ) {
        this.$msg({
          type: "success",
          message: "正在获取题目，请稍等！",
          duration: 1000,
          offset: 80,
        });
        setTimeout(async () => {
          await this.getproblemlist();
          if (this.isgetprolist) {
            this.$msg({
              type: "success",
              message: "题目加载完成！",
              duration: 1000,
              offset: 80,
            });
          } else {
            this.$msg({
              type: "error",
              message: "题目获取失败，正在重试！",
              duration: 1600,
              offset: 80,
            });
          }
          this.updateEcharts();
          if (this.isacm == true || this.issqs) {
            this.lookacmrank();
          } else if (this.isioi || this.isoi) {
            this.lookoirank();
          }
        }, 1000);
      }
      if (this.begin <= this.nowtime && this.end >= this.nowtime) {
        //竞赛开始且未结束
        !this.isbegin && (this.isbegin = true);
        this.isend && (this.isend = false);
        if (this.totaltime % 4 === 0) {
          this.updateEcharts();
          if (this.isacm == true || this.issqs) {
            this.lookacmrank();
          } else if (this.isioi || this.isoi) {
            this.lookoirank();
          }
        }
      } else if (this.end < this.nowtime) {
        // 竞赛结束
        !this.isbegin && (this.isbegin = true);
        !this.isend && (this.isend = true);
        if (this.totaltime % 4 === 0) {
          this.updateEcharts();
          if (this.isacm == true || this.issqs) {
            this.lookacmrank();
          } else if (this.isioi || this.isoi) {
            this.lookoirank();
          }
        }
      }
    },
    async updateEcharts() {
      if (this.contestdata.allpeople > 40) {
        return;
      }
      if (!this.isend && this.isoi && !this.is_my_contest) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/getContestRank",
        portType: {
          process: "8788",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.userdata = [];
      this.userdata = res.peopledata;
      this.timedata = res.timedata;
      this.resrank = res?.data;
      this.echartsnum = this.userdata.length;
      if (res?.code == 1 && this.type != "OI") {
        this.getrank();
      }
    },
    async getrank() {
      if (
        !this.isbegin ||
        this.contestdata.allpeople <= 0 ||
        this.contestdata.allpeople > 40 ||
        this.type == "OI"
      ) {
        return;
      }
      let echarts_dom = document.getElementById("rankchart");
      if (!echarts_dom) {
        this.echarts_finish = false;
        return;
      }
      let trank = [];
      for (let i = 0; i < this.echartsnum; ++i) {
        trank[i] = {
          symbol: "none", //去点
          smooth: true, //平滑
          smoothMonotone: "x", //x轴单调
          name: this.userdata[i],
          type: "line",
          data: this.resrank[i],
        };
      }
      let myChart = echarts.init(echarts_dom);
      myChart.setOption({
        title: {
          text: "", //表格题目
          textStyle: {
            color: "#ffffff",
          },
        },
        tooltip: {
          trigger: "axis",
          textStyle: {
            color: "rgba(0,0,0,0.8)",
          },
        },
        legend: {
          data: this.userdata.length >= 20 ? [] : this.userdata,
          icon: "roundRect", //icon为圆角矩形
          textStyle: {
            color: "#ffffff",
          },
        },
        grid: {
          left: "3%",
          right: "4%",
          bottom: "3%",
          containLabel: true,
        },
        toolbox: {
          show: true,
          feature: {
            saveAsImage: {
              show: true,
              excludeComponents: ["toolbox"],
              pixelRatio: 2,
            },
          },
        },
        xAxis: {
          type: "category",
          boundaryGap: false,
          data: this.timedata,
          axisLabel: {
            //修改坐标系字体颜色
            show: true,
            textStyle: {
              color: "#ffffff",
            },
          },
          splitLine: {
            show: false,
          },
        },
        yAxis: {
          type: "value",
          axisLabel: {
            //修改坐标系字体颜色
            show: true,
            textStyle: {
              color: "#ffffff",
            },
          },

          // 网格线样式
          splitLine: {
            show: true,
            lineStyle: {
              color: "rgba(0,0,0,0.4)",
            },
          },
        },
        series: trank,
        tooltip: {
          // 鼠标悬浮提示框显示 X和Y 轴数据
          trigger: "axis",
          backgroundColor: "rgba(32, 33, 36,.7)",
          borderColor: "rgba(32, 33, 36,0.20)",
          borderWidth: 1,
          textStyle: {
            // 文字提示样式
            color: "#fff",
            fontSize: "14",
          },
          axisPointer: {
            // 坐标轴虚线
            type: "cross",
            label: {
              backgroundColor: "#6a7985",
            },
          },
          position: function (point, params, dom, rect, size) {
            //其中point为当前鼠标的位置，size中有两个属性：viewSize和contentSize，分别为外层div和tooltip提示框的大小
            let x = point[0];
            let y = point[1];
            let boxWidth = size.contentSize[0];
            let boxHeight = size.contentSize[1];
            let posX = 0; //x坐标位置
            let posY = 0; //y坐标位置
            if (x < boxWidth) {
              //左边放不开
              posX = 5;
            } else {
              //左边放的下
              posX = x - boxWidth;
            }
            if (y < boxHeight) {
              //上边放不开
              posY = 5;
            } else {
              //上边放得下
              posY = y - boxHeight;
            }
            return [posX, posY];
          },
        },
      });
      if (!this.echarts_finish) {
        setTimeout(() => {
          this.echarts_finish = true;
        }, 0);
      }
    },
    toback() {
      this.$router.go(-1);
    },
    toonepro(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/oneproblem",
          query: {
            path: urlencode(id, "gbk"),
            contest: urlencode(this.contestid, "gbk"),
          },
        });
    },
    async getproblemlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/lookContestProblem",
        portType: {
          process: "8796",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.problemList = res?.data;
        this.isgetprolist = true;
      } else {
        !this.showone_join_msg &&
          (this.showone_join_msg = true) &&
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
      }
    },

    async getdata() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/userLookContest",
        portType: {
          process: "8796",
        },
        data: {
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.contestdata = res?.data;
        this.type = res?.data?.type;
        /* 允许查看排名 */
        if (this.type == "ACM") {
          this.lookacmrank();
          this.isshow = true;
          this.isacm = true;
          this.isoi = false;
          this.isioi = false;
          this.issqs = false;
        } else if (this.type == "OI") {
          this.isshow = false;
          this.isoi = true;
          this.isacm = false;
          this.isioi = false;
          this.issqs = false;
        } else if (this.type == "IOI") {
          this.lookoirank();
          this.isshow = true;
          this.isioi = true;
          this.isoi = false;
          this.isacm = false;
          this.issqs = false;
        } else if (this.type == "SQS") {
          this.lookacmrank();
          this.isshow = true;
          this.issqs = true;
          this.isioi = false;
          this.isoi = false;
          this.isacm = false;
        } else {
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 800,
            offset: 80,
          });
        }
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      }
    },
    async joincontest() {
      this.canclick = false;
      this.isseepassword = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/joinContest",
        portType: {
          process: "8796",
        },
        data: {
          contest_id: this.contestid,
          password: this.password,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.canclick = true;
      this.password = "";
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1000,
          offset: 80,
        });
        this.isjoin = true;
        this.isseetip = true;
        this.isgetprolist = false;
        this.echartsnum = 0;
        this.myrank = 0;
        this.limit = 20;
        this.page = 1;
        this.isseecode = false;
        this.isacm = false;
        this.isoi = false;
        this.isioi = false;
        this.issqs = false;
        this.isshow = true;
        await this.getdata();
        await this.judgeisjoin();
        await this.getproblemlist();
        await this.updateEcharts();
        this.totaltime = 0;
        this.totaltime++;
        //当前时间戳
        this.nowtime = Date.now();
        //开始时间戳
        this.begin = new Date(this.contestdata.begin);
        this.begin = this.begin.getTime();
        //结束时间戳
        this.end = new Date(this.contestdata.end);
        this.end = this.end.getTime();
        if (this.begin <= this.nowtime && this.end >= this.nowtime) {
          this.isbegin = true;
          this.isend = false;
          await this.updateEcharts();
        } else if (this.end < this.nowtime) {
          this.isbegin = true;
          this.isend = true;
          await this.updateEcharts();
        }
        if (this.isacm == true) {
          await this.lookacmrank();
          this.isshow = true;
        }
      } else {
        this.isjoin = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1000,
          offset: 80,
        });
      }
    },
  },
  computed: {
    prop() {
      let data = {
        subfield: false, // 单双栏模式
        defaultOpen: "preview", //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: false,
        toolbarsFlag: false, //工具栏
        scrollStyle: true,
        codeStyle: "atom-one-dark",
        boxShadow: false,
        ishljs: true,
        tabSize: 4,
        toolbarsBackground: "rgba(0,0,0,0)",
        editorBackground: "rgba(0,0,0,0)",
        previewBackground: "rgba(0,0,0,0)",
        fontSize: "1.06rem",
        navigation: false,
      };
      return data;
    },
  },
};
</script>

<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";

.el-button {
  padding: 0.6rem 1rem !important;
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  color: rgba(var(--ltpp-light-color), 1) !important;
  font-size: 1.06rem;
}

tr td,
th {
  border: 0.16rem solid #f56c6c;
}
td {
  font-size: 1.06rem;
  font-weight: bold;
}
::v-deep .el-table,
::v-deep .el-table__expanded-cell {
  background-color: transparent !important;
}
/* 表格内背景颜色 */
::v-deep .el-table th,
::v-deep .el-table tr,
::v-deep .el-table td {
  background-color: transparent !important;
}

::v-deep .el-table__row > td {
  border: none !important;
}
::v-deep .el-table::before {
  height: 0px !important;
}
::v-deep .el-table__cell,
.is-leaf {
  border: none !important;
}
</style>