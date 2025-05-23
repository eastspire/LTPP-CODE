/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 1491579574@qq.com
 * @LastEditTime: 2023-06-04 00:02:38
 * @FilePath: \LTPP-CODE\Frontend\src\plugins\element.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 1491579574@qq.com, All Rights Reserved.
 */
import Vue from 'vue';
import {
  Button,
  Form,
  Carousel,
  Footer,
  FormItem,
  Input,
  Message,
  Select,
  Option,
  Menu,
  Submenu,
  MenuItemGroup,
  MenuItem,
  Table,
  TableColumn,
  DropdownMenu,
  Container,
  Header,
  Main,
  Aside,
  Row,
  Card,
  Col,
  pagination,
  Pagination,
  Dialog,
  Descriptions,
  MessageBox,
  Image,
  Avatar,
  DescriptionsItem,
  Backtop,
  Tag,
  Drawer,
  CarouselItem,
  Upload,
  DatePicker,
  Checkbox,
  Switch,
  Divider,
  Calendar,
  Loading,
  alert,
  tooltip,
  Notification,
  Autocomplete,
  Progress,
  TabPane,
  Tabs,
} from 'element-ui';

Vue.use(Loading.directive); //解决v-loading异步不生效
Vue.use(Tabs);
Vue.use(TabPane);
Vue.use(tooltip);
Vue.use(Autocomplete);
Vue.use(alert);
Vue.use(Progress);
Vue.use(Footer);
Vue.use(Loading);
Vue.use(Calendar);
Vue.use(Divider);
Vue.use(CarouselItem);
Vue.use(Switch);
Vue.use(Checkbox);
Vue.use(DatePicker);
Vue.use(Upload);
Vue.use(Carousel);
Vue.use(Drawer);
Vue.use(Tag);
Vue.use(Backtop);
Vue.use(Avatar);
Vue.use(Image);
Vue.use(Descriptions);
Vue.use(DescriptionsItem);
Vue.use(Dialog);
Vue.use(Pagination);
Vue.use(pagination);
Vue.use(Col);
Vue.use(Card);
Vue.use(Row);
Vue.use(Button);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Input);
Vue.use(Select);
Vue.use(Option);
Vue.use(Menu);
Vue.use(Submenu);
Vue.use(MenuItemGroup);
Vue.use(MenuItem);
Vue.use(Table);
Vue.use(TableColumn);
Vue.use(DropdownMenu);
Vue.use(Container);
Vue.use(Header);
Vue.use(Main);
Vue.use(Aside);

Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$msg = Message;
Vue.prototype.$notice = Notification;
