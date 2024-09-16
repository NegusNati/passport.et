import type { IconType } from "react-icons/lib";

import OldPortfolioUrl from "assets/projects/old-portfolio.png?url";
import SpotifyNext13Url from "assets/projects/spotify_next13.png?url";
import TheLabzUrl from "assets/projects/the-labz.jpg?url";
import TwitterCloneUrl from "assets/projects/twitter-clone.png?url";
import PassportWelcomerUrl from "assets/projects/passport.png?url";
import PassportDashboardUrl from "assets/projects/pass_dashboardpng.png?url";
import VictorAppUrl from "assets/projects/app_victor.png?url";
import PassportTableUrl from "assets/projects/pass_table.png?url";
import LoadBalancerUrl from "assets/projects/loadballancer.png?url";
import MobileAppUrl from "assets/projects/mob.png?url";
import WebDeliveryUrl from "assets/projects/web_delivery.png?url";
import LeadProjectUrl from "assets/projects/lead.png?url";
import OutletProjectUrl from "assets/projects/outlet.png?url";

import AirtableIcon from "icons/AirtableIcon";
import AwsIcon from "icons/AwsIcon";
import BuildIcon from "icons/BuildIcon";
import NextjsIcon from "icons/NextjsIcon";
import PrismaIcon from "icons/PrismaIcon";
import ReactIcon from "icons/ReactIcon";
import ReduxIcon from "icons/ReduxIcon";
import StorybookIcon from "icons/StorybookIcon";
import StyledComponentsIcon from "icons/StyledComponentsIcon";
import TailwindIcon from "icons/TailwindIcon";
import TestIcon from "icons/TestIcon";
import DockerIcon from "icons/DockerIcon";
import trpcIcon from "icons/DockerIcon";
import TypescriptIcon from "icons/TypescriptIcon";

import LaravelHorizonIcon from "icons/Laravelhorizon";
import LaravelIcon from "icons/Laravel";
import ExpoIcon from "icons/expo";
import ExpressIcon from "./../icons/Express";
import MongoDBIcon from "icons/MongoDB";
import NodeIcon from "icons/Node";
import PostgresqlIcon from "icons/Postgressql";
import PostmanIcon from "icons/Postman";
import SQLiteIcon from "icons/SQLite";
import LinuxIcon from "icons/Linux";
import JavaIcon from "icons/Java";
import JavaScriptIcon from "icons/JavaScript";
import FlutterIcon from "icons/Flutter";
import DartIcon from "icons/Dart";
import MapIcon from "icons/Maps";
import TomcatIcon from "icons/Tomcat";

const techTypes = [
  "Next.js",
  "TypeScript",
  "Vite",
  "Jest + RTL",
  "React",
  "Redux",
  "Prisma",
  "Styled Components",
  "TanStack Query",
  "Storybook",
  "Airtable",
  "AWS",
  "@craftjs/core",
  "Tailwind CSS",
  "tRPC",
  "Svelte",
  "Docker",
  "Laravel",
  "Laravel Horizon",
  "Expo",
  "Express",
  "MongoDB",
  "Node.js",
  "PostgreSQL",
  "Nginx",
  "Postman",
  "SQLite",
  "Linux",
  "Java",
  "Flutter",
  "Dart",
  "Google Maps",
  "Appache Tomcat",
  "JavaScript",
  "TypeGraphQL"
] as const;

type TechTuple = typeof techTypes;

export type ProjectTech = { tech: TechTuple[number]; icon: IconType };

export type Project = {
  title: string;
  description: string;
  image: string;
  url: string;
  githubUrl?: string;
  techs: ProjectTech[];
  isPinned?: boolean;
};

const reactTech: ProjectTech = { tech: "React", icon: ReactIcon };
const tanStackQueryTech: ProjectTech = { tech: "TanStack Query", icon: ReactIcon };
const tailwindCssTech: ProjectTech = { tech: "Tailwind CSS", icon: TailwindIcon };
const dockerTech: ProjectTech = { tech: "Docker", icon: DockerIcon };
const laravelTech: ProjectTech = { tech: "Laravel", icon: LaravelIcon };
const laravelHorizonTech: ProjectTech = { tech: "Laravel Horizon", icon: LaravelHorizonIcon };
const expoTech: ProjectTech = { tech: "Expo", icon: ExpoIcon };
const expressTech: ProjectTech = { tech: "Express", icon: ExpressIcon };
const mongoDBTech: ProjectTech = { tech: "MongoDB", icon: MongoDBIcon };
const nodeTech: ProjectTech = { tech: "Node.js", icon: NodeIcon };
const postgressqlTech: ProjectTech = { tech: "PostgreSQL", icon: PostgresqlIcon };
const sqliteTech: ProjectTech = { tech: "SQLite", icon: SQLiteIcon };
const linuxTech: ProjectTech = { tech: "Linux", icon: LinuxIcon };
const javaTech: ProjectTech = { tech: "Java", icon: JavaIcon };
const javascriptTech: ProjectTech = { tech: "JavaScript", icon: JavaScriptIcon };
const flutterTech: ProjectTech = { tech: "Flutter", icon: FlutterIcon };
const dartTech: ProjectTech = { tech: "Dart", icon: DartIcon };
const mapTech: ProjectTech = { tech: "Google Maps", icon: MapIcon };
const tomcatTech: ProjectTech = { tech: "Appache Tomcat", icon: TomcatIcon };

const nextjsTech: ProjectTech = { tech: "Next.js", icon: NextjsIcon };
const typeScriptTech: ProjectTech = { tech: "TypeScript", icon: TypescriptIcon };
const prismaTech: ProjectTech = { tech: "Prisma", icon: PrismaIcon };
const styledComponentsTech: ProjectTech = { tech: "Styled Components", icon: StyledComponentsIcon };
// const reactTech: ProjectTech = { tech: "React", icon: ReactIcon };
const reduxTech: ProjectTech = { tech: "Redux", icon: ReduxIcon };
const testTech: ProjectTech = { tech: "Jest + RTL", icon: TestIcon };
// const tanStackQueryTech: ProjectTech = { tech: "TanStack Query", icon: ReactIcon };
const storybookTech: ProjectTech = { tech: "Storybook", icon: StorybookIcon };
const airtableTech: ProjectTech = { tech: "Airtable", icon: AirtableIcon };
const awsTech: ProjectTech = { tech: "AWS", icon: AwsIcon };
const craftjsTech: ProjectTech = { tech: "@craftjs/core", icon: BuildIcon };
const trpcTech: ProjectTech = { tech: "tRPC", icon: trpcIcon };
const graphqlTech: ProjectTech = { tech: "Docker", icon: DockerIcon };
const typeGraphqlTech: ProjectTech = { tech: "TypeGraphQL", icon: DockerIcon };
// const viteReactTech: ProjectTech = { tech: "Vite", icon: ViteIcon };
// const svelteTech: ProjectTech = { tech: "Svelte", icon: SvelteIcon };

export const projects: Project[] = [
    {
        title: "Victor App/ERP",
        description:
          "Victor App is a project I am currently working on and \n actively maintaining. it is a role based system to orchestrate \n the buisness logic of one of the highest revenue generating company in Addis.with feature like\n  - Quotation & Agremment Generation \n - Ordering and Order tracking \n - Sales performance reports and integration with Installation system \n  - Deposit management & dedicated finance operations ",
        image: VictorAppUrl,
        url: "https://app.victor-door.com/",
        githubUrl: "https://app.victor-door.com/",
        techs: [
          laravelTech,
          reduxTech,
          typeScriptTech,
          postgressqlTech,
          reactTech,
          tailwindCssTech,
          nodeTech,
          tanStackQueryTech,
          linuxTech,
          dockerTech,

        ],
        isPinned: true
      },
  {
    title: "PassportET",
    description:
      "A simple way to look if your passport is ready \n to collect and which day of the week you should collect. ✈️ \n It has multiple cool things underneath, like : \n - PDF parsing of official passport data published by Ethiopian gov. \n - Queues, i used Laravel Queues to assign background jobs of PDF parsing. \n - Rate Limiting : to prevent exessive requests. \n - Roles & Permission: a subscription system to allow users to access the system based on their role and permission. ",
    image: PassportDashboardUrl,
    url: "https://passport.et/",
    githubUrl: "https://github.com/NegusNati/passport.et",
    techs: [
      laravelTech,
      laravelHorizonTech,
      sqliteTech,
      dockerTech,
      reactTech,
      tailwindCssTech,
      nodeTech,
      tanStackQueryTech,
      linuxTech
    ],
    isPinned: true
  },
  {
    title: "Elilta Trading Lead Conversion system",
    description:
      "his project was based on specific user requirement that the enterprise had and those were :   \n - To provide a robust and engaging way for sales Reps to get and convert leads \n- To have the previous records of sales be displayed for the sales rep \n -To have appointment and follow-up management, also sent SMS messages to notify appointment data for the sales rep \n - To have daily, weekly and monthly minimum conversion rate and have a `LEADER BOARD` to incentivize sales reps with bonuses.\n - To have a dashboard to track the performance of each sales rep ( by time spent on calls, conversion rates, . ",
    image: LeadProjectUrl,
    url: "https://github.com/NegusNatip",
    githubUrl: "https://github.com/NegusNati",
    techs: [
      reactTech,
      nodeTech,
      expressTech,
      mongoDBTech,
      mapTech,
      tailwindCssTech,
      linuxTech,
      javascriptTech,
      expoTech
    ],
    isPinned: true
  },

  {
    title: "Food Delivery App Flutter",
    description:
      "This is a food delivery mobile app built with Flutter. \n It allows users to browse through available restaurants, select a menu item or items, and place an order for delivery.\n Built with integration of live Google Maps API to track orders and display estimated delivery time.\n and Payment integration with Chapa Payment Gatway, with features like : \n - Browse nearby restaurants: Users can see a list of nearby restaurants, view the menu and select an item to add to their cart. \n - Check out: Users can review their order, add delivery details, and complete checkout.\n - Payment integration: Secure online payments can be made with a chosen payment method.\n - Order Tracking: Users can track their order status in real-time. ",
    image: MobileAppUrl,
    url: "https://github.com/NegusNati/food_delivery_app_flutter",
    githubUrl: "https://github.com/NegusNati/food_delivery_app_flutter",
    techs: [flutterTech, dartTech, mapTech, javaTech],
    isPinned: true
  },

  {
    title: "Elilta Trading Outlet Managment System ",
    description:
      "This outlet management for ordering and managing customer for \nThis project was based on specific user requirement that the enterprise had and those were: \n - To have the previous records of sales be displayed for the sales rep.  \n packed water company to distribute to thousands of outlets(small companies/stores). \n - Display the absolute location of all the outlets for sales reps before they make a call. \n - Send automatic SMS message to Delivery person the Order information with Google Map location of the Outlet.\n - To have daily, weekly and monthly minimum conversion rate and have a `LEADER BOARD` to incentivize sales reps with bonuses.\n- To have a dashboard to track the performance of each sales rep ( by time spent on calls, conversion rates, ...) ",
    image: OutletProjectUrl,
    url: "https://github.com/NegusNatip",
    githubUrl: "https://github.com/NegusNati",
    techs: [
      reactTech,
      nodeTech,
      expressTech,
      mongoDBTech,
      mapTech,
      tailwindCssTech,
      javascriptTech
    ],
  },
  {
    title: "Food Delivery Web App",
    description:
      "This is a food delivery web app built with React and Laravel. \n It allows users to manage orders, revenue report(to csv), activity logs,automatic comunication with delivery boy app. \n it is a role and permission based system with diffrent roles like resturant owners, cahiers and super admin.         able restaurants, select a menu item or items, and place an order for delivery.\n ",
    image: WebDeliveryUrl,
    url: "https://github.com/NegusNati/food_delivery_laravel",
    githubUrl: "https://github.com/NegusNati/food_delivery_laravel",
    techs: [
      reactTech,
      laravelTech,
      laravelHorizonTech,
      tailwindCssTech,
      linuxTech,
      javascriptTech,
      mapTech
    ]
  },

  {
    title: "Load Balancer",
    description:
      "Design and Implementation of a Distributed Load Balancing System \n  that can be used to route trafic with Java Load Balancer \n This a simple program to demonstarte Load balancing in java using Round Robin and Random algorithms with the help of multiple built in methods and user defined methods. \n It is Designed and implemented for our Tomcat and Glassfish servers",
    image: LoadBalancerUrl,
    url: "https://github.com/NegusNati/distributed_Load_Balancing",
    githubUrl: "https://github.com/NegusNati/distributed_Load_Balancing",
    techs: [javaTech, tomcatTech, awsTech]
  }
];
