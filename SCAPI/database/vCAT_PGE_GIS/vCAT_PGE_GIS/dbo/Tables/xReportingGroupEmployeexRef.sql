CREATE TABLE [dbo].[xReportingGroupEmployeexRef] (
    [ReportingGroupEmployeeID] INT           IDENTITY (1, 1) NOT NULL,
    [UserUID]                  VARCHAR (100) NULL,
    [ReportingGroupUID]        VARCHAR (100) NULL,
    [RoleUID]                  VARCHAR (100) NULL,
    [CreatedUserUID]           VARCHAR (100) NULL,
    [ModifiedUserUID]          VARCHAR (100) NULL,
    [CreateDatetime]           DATETIME      CONSTRAINT [DF_xReportingGroupEmployeexRef_CreateDatetime] DEFAULT (getdate()) NULL,
    [ModifiedDatetime]         DATETIME      NULL,
    [Revision]                 INT           CONSTRAINT [DF_xReportingGroupEmployeexRef_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]               BIT           CONSTRAINT [DF_xReportingGroupEmployeexRef_ActiveFlag] DEFAULT ((1)) NULL
);

