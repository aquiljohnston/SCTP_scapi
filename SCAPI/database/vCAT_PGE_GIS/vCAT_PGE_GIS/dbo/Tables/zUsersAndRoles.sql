CREATE TABLE [dbo].[zUsersAndRoles] (
    [First]            NVARCHAR (30) NULL,
    [Last]             NVARCHAR (30) NULL,
    [FirstLoginDate]   DATETIME      NULL,
    [LastLoginDate]    DATETIME      NULL,
    [LoginID]          INT           NULL,
    [LANID]            NVARCHAR (60) NULL,
    [PersonID]         INT           NOT NULL,
    [CometTrackerName] NVARCHAR (30) NULL,
    [CometMatch]       VARCHAR (3)   NOT NULL,
    [Division]         VARCHAR (200) NULL,
    [Role]             VARCHAR (100) NULL,
    [SurveyorType]     VARCHAR (100) NULL
);

