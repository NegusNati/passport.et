import laravelIcon from "assets/icons/laravel.svg";
import dockerIcon from "assets/icons/docker.svg";
import linuxIcon from "assets/icons/linux.svg";
import javaIcon from "assets/icons/java.svg";
import cssIcon from "assets/icons/css.svg";
import htmlIcon from "assets/icons/html.svg";
import jsIcon from "assets/icons/js.svg";
import phpIcon from "assets/icons/php.svg";
import hetznerIcon from "assets/icons/hetzner.svg";
import gitIcon from "assets/icons/git.svg";
import pythonIcon from "assets/icons/python.svg";
import nodeIcon from "assets/icons/node.svg";
import expressIcon from "assets/icons/express.svg";
import nginxIcon from "assets/icons/NginxIcon.svg";
import mongodbIcon from "assets/icons/mongodb.svg";
import sqliteIcon from "assets/icons/sqlite.svg";
import mysqlIcon from "assets/icons/mysql.svg";
import postgressqlIcon from "assets/icons/postgressql.svg";

import cypressIcon from "assets/icons/cypress.svg";
import eslintIcon from "assets/icons/eslint.svg";
import gatsbyIcon from "assets/icons/gatsby.svg";
import graphqlIcon from "assets/icons/graphql.svg";
import jestIcon from "assets/icons/jest.svg";
import materialUiIcon from "assets/icons/material-ui.svg";
import mswIcon from "assets/icons/msw.svg";
import nextjsIcon from "assets/icons/next-js.svg";
import playwrightIcon from "assets/icons/playwright.svg";
import prettierIcon from "assets/icons/prettier.svg";
import prismaIcon from "assets/icons/prisma.svg";
import reactIcon from "assets/icons/react.svg";
import reactHookFormIcon from "assets/icons/react-hook-form.svg";
import reactQueryIcon from "assets/icons/react-query.svg";
import reactTestingLibraryIcon from "assets/icons/react-testing-library.svg";
import reduxIcon from "assets/icons/redux.svg";
import storybookIcon from "assets/icons/storybook.svg";
import styledComponentsIcon from "assets/icons/styled-components.svg";
import svelteIcon from "assets/icons/svelte.svg";
import tailwindcssIcon from "assets/icons/tailwind-css.svg";
import trpcIcon from "assets/icons/trpc.svg";
import typescriptIcon from "assets/icons/typescript.svg";
import viteIcon from "assets/icons/vite.svg";
import yupIcon from "assets/icons/yup.svg";
import zodIcon from "assets/icons/zod.svg";
import zustandIcon from "assets/icons/zustand.svg";


type Skill = {
  src: string;
  website: string;
  text: string;
};

export const skills: {
  experiencedWith: Skill[];
  currentlyLearning: Skill[];
  wantToLearn: Skill[];
} = {
  experiencedWith: [
    {
      src: laravelIcon,
      website: "https://laravel.com",
      text: "Laravel"
    },
    {
      src: reactIcon,
      website: "https://reactjs.org",
      text: "React.js"
    },
    {
      src: tailwindcssIcon,
      website: "https://tailwindcss.com",
      text: "Tailwind CSS"
    },
    {
      src: reactIcon,
      website: "https://reactnative.dev",
      text: "React Native"
    },
    {
      src: nodeIcon,
      website: "https://nodejs.org",
      text: "Node.js"
    },
    {
      src: expressIcon,
      website: "https://expressjs.com/",
      text: "Express.js"
    },
    {
      src: mongodbIcon,
      website: "https://www.mongodb.com/",
      text: "MongoDB"
    },
    {
      src: sqliteIcon,
      website: "https://www.sqlite.org/",
      text: "SQLite"
    },
    {
      src: mysqlIcon,
      website: "https://www.mysql.com/",
      text: "MySQL"
    },
    {
      src: postgressqlIcon,
      website: "https://www.postgresql.org/",
      text: "PostgreSQL DB"
    },
    {
      src: reduxIcon,
      website: "https://redux-toolkit.js.org",
      text: "Redux Toolkit"
    },

    {
      src: reactQueryIcon,
      website: "https://tanstack.com/query/latest",
      text: "React Query"
    },
    {
      src: reactHookFormIcon,
      website: "https://react-hook-form.com",
      text: "React Hook Form"
    },
    {
      src: eslintIcon,
      website: "https://eslint.org",
      text: "ESLint"
    },
    {
      src: prettierIcon,
      website: "https://prettier.io",
      text: "Prettier"
    },
    {
      src: viteIcon,
      website: "https://vitejs.dev",
      text: "Vite"
    },
    {
      src: reactIcon,
      website: "https://reactnative.dev",
      text: "React Native"
    },
    {
      src: linuxIcon,
      website: "https://linux.org",
      text: "Linux"
    },
    {
      src: javaIcon,
      website: "https://java.com",
      text: "Java"
    },
    {
      src: cssIcon,
      website: "https://developer.mozilla.org/en-US/docs/Web/CSS",
      text: "CSS 3"
    },
    {
      src: htmlIcon,
      website: "https://developer.mozilla.org/en-US/docs/Web/html",
      text: "HTML 5"
    },
    {
      src: jsIcon,
      website: "https://developer.mozilla.org/en-US/docs/Web/JavaScript",
      text: "JavaScript"
    },
    {
      src: phpIcon,
      website: "https://php.net",
      text: "PHP"
    },
    {
      src: hetznerIcon,
      website: "https://hetzner.com",
      text: "Hetzner Deployment | vps"
    },
    {
      src: gitIcon,
      website: "https://git.org",
      text: "git Version control"
    },
    {
      src: pythonIcon,
      website: "https://python.org",
      text: "Python"
    }
  ],
  currentlyLearning: [
    {
      src: typescriptIcon,
      website: "https://www.typescriptlang.org",
      text: "TypeScript"
    },
    {
      src: dockerIcon,
      website: "https://docker.org",
      text: "Docker"
    },
    {
      src: dockerIcon,
      website: "https://docker.org",
      text: "Docker Compose"
    }
  ],

  wantToLearn: [
    {
      src: svelteIcon,
      website: "https://svelte.dev",
      text: "Svelte"
    },
    {
      src: prismaIcon,
      website: "https://www.prisma.io",
      text: "Prisma"
    },
    {
      src: trpcIcon,
      website: "https://trpc.io",
      text: "tRPC"
    },
    {
      src: zustandIcon,
      website: "https://zustand-demo.pmnd.rs",
      text: "Zustand ❤️"
    }
  ]
};
