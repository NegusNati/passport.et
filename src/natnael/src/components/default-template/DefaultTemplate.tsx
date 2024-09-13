import { Footer } from "shared/Footer";
import { Header } from "shared/Header";

import { DefaultAboutMeSection } from "./DefaultAboutMeSection";
import { DefaultContactSection } from "./DefaultContactSection";
import { DefaultProjectsSection } from "./DefaultProjectsSection";
import { DefaultSkillsSection } from "./DefaultSkillsSection";

export const DefaultTemplate = () => {
  return (
    <main className="relative flex flex-col">
      <Header />
      <div className="flex flex-col max-w-[1240px] w-full mx-auto p-[30px]">
        <div className="absolute inset-0 max-h-[67vh] bg-gradient-radial bg-[length:30px_30px] bg-[0_0,15px_15px] z-[-1px] after:content-[''] after:absolute after:left-0 after:right-0 after:bottom-0 after:h-[500px] after:bg-linear-gradient before:content-[''] before:absolute before:left-0 before:right-0 before:top-0 before:h-[50px] before:bg-gradient-to-b before:from-[#060606] before:to-[transparent]" />
        <DefaultAboutMeSection />
        <DefaultSkillsSection />
        <DefaultProjectsSection />
        <DefaultContactSection />
      </div>
      <Footer />
    </main>
  );
};
