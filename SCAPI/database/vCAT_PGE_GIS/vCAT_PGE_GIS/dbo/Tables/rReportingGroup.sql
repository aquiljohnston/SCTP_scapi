CREATE TABLE [dbo].[rReportingGroup] (
    [rReportingGroupID] INT           IDENTITY (1, 1) NOT NULL,
    [ReportingGroupUID] VARCHAR (100) NULL,
    [ProjectID]         INT           NULL,
    [CreatedUserUID]    VARCHAR (100) NULL,
    [ModifiedUserUID]   VARCHAR (100) NULL,
    [CreatedDateTime]   DATETIME      CONSTRAINT [DF_rReportingGroup_CreatedDateTime] DEFAULT (getdate()) NULL,
    [ModifiedDateTime]  DATETIME      NULL,
    [GroupName]         VARCHAR (50)  NULL,
    [Revision]          INT           CONSTRAINT [DF_rReportingGroup_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]        BIT           CONSTRAINT [DF_rReportingGroup_ActiveFlag] DEFAULT ((1)) NULL,
    [IsGroupFlag]       BIT           CONSTRAINT [DF_rReportingGroup_IsGroupFlag] DEFAULT ((0)) NULL
);

